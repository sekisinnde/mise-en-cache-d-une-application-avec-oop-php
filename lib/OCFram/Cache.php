<?php
namespace OCFram;
 class Cache extends ApplicationComponent implements \SplObserver
{
	protected $dataDirname,
		$viewsDirname,
		$duration;//Durée de mise en cache générale, en minutes
 	public function __construct(Application $app, $dataDirname, $viewsDirname, $duration)
	 {
	 	parent::__construct($app);
	 	$this->setDataDirname($dataDirname);
	 	$this->setViewsDirname($viewsDirname);
	 	$this->setDuration($duration);
	 }
 	//Setters
	public function setDataDirname(string $name)
	 {
	 	$this->dataDirname = $name;
	 }
 	public function setViewsDirname(string $name)
	 {
	 	$this->viewsDirname = $name;
	 }
 	public function setDuration(int $duration)
	 {
		$this->duration = $duration;
	 }
 	//Lecture-écriture des données : methodes utilisees dans les ModulesControllers au moment de la recuperation des donnees
	public function writeData(string $fileName, $data)
	 {
	 	return file_put_contents($this->dataDirname. '/'. $fileName, serialize($data));
	 }
 	public function readData(string $fileName)
	 {
	 	$file = $this->dataDirname. '/'. $fileName;
	 	//Si le fichier n'existe pas
	 	if(!file_exists($file))
	 	 {
	 	 	return false;
	 	 }
		
		$lifetime = (time()-filemtime($file))/60;
		//Si le fichier a expire
		if($lifetime > $this->duration)
		 {
		 	unlink($file);
		 	return false;
		 }
 		return unserialize(file_get_contents($file));
	 }
 	//lecture-écriture des vues
		//Méthode appelée dans Page::getGeneratedPage() si la vue est concernée par la mise en cache (existe dans l'array renvoyé par FrontendApplication::createCache())
	public function writeView(string $fileName, string $view) 
	 {
	 	$file = $this->viewsDirname. '/'. $fileName;
	 	return file_put_contents($file, $view);
	 }
	 	//Methode appelee dans les Applications. Si la vue n'existe pas -> execution du controller et generation de la page. Si la vue existe recuperation de la page en cache.
	public function readView(string $fileName)
	 {
	 	$file = $this->viewsDirname. '/'. $fileName;
 	 	if(!file_exists($file))
	 	 {
	 	 	return false;
	 	 }
		
		$lifetime = (time()-filemtime($file))/60;
 		if($lifetime > $this->duration)
		 {
		 	unlink($file);
		 	return false;
		 }
 		return file_get_contents($file); // A stocker dans une variable nommée $content utilisée dans la méthode Page::getCachePage() de Page dont le résultat sera passé à la méthode send() de HTTPRequest
	 }
 	//Fonctions de suppression appelees dans la methode update() de l'Observer du Pattern Observer (Cache) 
	public function deleteData(string $fileName)
	 {
		$file = $this->dataDirname. '/'. $fileName;
		if(file_exists($file))
		{
			unlink($file);
		}
	 }
 	public function deleteView(string $fileName)
	 {
	 	$file = $this->viewsDirname. '/'. $fileName;
		if(file_exists($file))
		{
			unlink($file);
		}
	 }
 	//Fonction du Pattern Observer appelée par les EntityManagerPDO
	public function update(\SplSubject $subject)
	 {
	 	$type = $subject->type();
	 	$action = $subject->actionValBuffer()['action'];
	 	$val = $subject->actionValBuffer()['val'];
 	 	if($type == 'News')
	 	 {
	 	 	if($action=='add' || $action=='modify' || $action=='delete')//Si une News a été ajoutée/modifiée ou supprimée
	 	 	 {
	 	 	 	//on supprime la vue index du module News de l'application Frontend
	 	 	 	$this->deleteView('Frontend_News_index');
	 	 	 	//on supprime la vue index du module News de l'application Frontend
	 	 	 	$this->deleteView('Backend_News_index');
 	 	 	 	if($action=='modify' || $action=='delete') //Si une News a été modifiée ou supprimée
	 	 	 	 {
	 	 	 	 	//On la supprime du cache
	 	 	 	 	$this->deleteData('news-'. $val);
 	 	 	 	 	if($action=='delete')//Si une News a été supprimée
	 	 	 	 	 {
	 	 	 	 	 	//On supprime ses commentaires
	 	 	 	 	 	$this->deleteData('comments-'. $val);
	 	 	 	 	 }
	 	 	 	 }
	 	 	 }
	 	 }
 		if($type == 'Comment')
	 	 {
	 	 	if($action=='add' || $action =='modify' || $action =='delete')//Si un commentaire a été ajouté/modifié ou supprimé
	 	 	 {
	 	 	 	//on supprime éventuellement une vue (par exemple show)
 				//On supprime la liste de la news à laquelle il est rattaché
				$this->deleteData('comments-'. (int)$val);
	 	 	 }
	 	 }
	 }
}