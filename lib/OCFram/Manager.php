<?php
namespace OCFram;
 abstract class Manager implements \SplSubject
{
  protected $dao;
  // Ceci un  tableau qui va contenir la ou les instances Cache qui observent le manager.
  protected $observers = [];
  //Ceci est un tableau qui sera modifié dans les méthodes du manager pour savoir le type d'action réalisée ('action'=>add/modify/delete) et les données qui vont avec ('val' => id de News, par exemple) sous forme d'array associatif -> permettra de le savoir dans l'Observer du Pattern Observer (Cache).
    //D'ou la necessite d'instancier les managers d'entity et que Cache soit un ApplicationComponent (pour y acceder).
  protected $actionValBuffer = array();
  //attribut  qui permettra dans l'observer du Pattern Observer (Cache) de savoir qu'est ce qui a subi une modification (News ou commentaire)
  protected  $type;
   public function __construct($dao)
  {
    $this->dao = $dao;
  }
   //Setters
 	public function setActionValBuffer(array $donnees)
	{
		$this->actionValBuffer = array();//On vide le buffer
		$this->actionValBuffer = $donnees;//On indique quelle action a été effectuée avec quelle donnée
	}
   public function setType(string $typeEntity)
   {
  	$this->type = $typeEntity;
   }
 	//Getters
  public function type()
   {
   	return $this->type;
   }
   public function actionValBuffer()
   {
   	return $this->actionValBuffer;
   }
   //Methodes SplSubject du Pattern observer, dès qu'il se passe quelque chose dans le manager le Cache en sera avertit
  public function attach(\SplObserver $observer)
  {
  	$this->observers[] = $observer;
  	return $this;//pour (éventuellement) ajouter plusieurs Observers à la suite
  }
   public function detach(\SplObserver $observer)
  {
  	if (is_int($key = array_search($observer, $this->observers, true)));
    {
         unset($this->observers[$key]);
    }
  }
   public function notify()
  {
  	foreach ($this->observers as $observer)
    {
      $observer->update($this);
    }
  }
}