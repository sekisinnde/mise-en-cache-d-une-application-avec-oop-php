<?php
namespace OCFram;
 abstract class BackController extends ApplicationComponent
{
  protected $action = '';
  protected $module = '';
  protected $page = null;
  protected $view = '';
  protected $managers = null;
  //Les managers des Entity sont instanciés pour pouvoir être utilisés dans le Pattern Observer
  //Auraient pu etre instanciés dans les BackController de chaque Application/Module si les Entity utilisées avaient été différentes
  protected $newsManager =null;
  protected $commentsManager=null;
   public function __construct(Application $app, $module, $action)
  {
    parent::__construct($app);
     $this->managers = new Managers('PDO', PDOFactory::getMysqlConnexion());
    $this->page = new Page($app);
     $this->setModule($module);
    $this->setAction($action);
    $this->setView($action); // 1 action = 1 vue (setView() en informe la page)
     //On instancie les managers
    $this->newsManager = $this->managers->getManagerOf('News');
    $this->commentsManager = $this->managers->getManagerOf('Comments');
     //On leur attache l'observateur Cache du Pattern Observer
    $this->newsManager->attach($this->app()->cache());
    $this->commentsManager->attach($this->app()->cache());
  }
   public function execute()
  {
    $method = 'execute'.ucfirst($this->action);
     if (!is_callable([$this, $method]))
    {
      throw new \RuntimeException('L\'action "'.$this->action.'" n\'est pas définie sur ce module');
    }
    //Execution de l'action pour laquelle le controller a été instancié (en lui passant la requete client)
    $this->$method($this->app->httpRequest());
  }
   //Getters
  public function page()
  {
    return $this->page;
  }
  public function module()
  {
    return $this->module;
  }
  public function action()
  {
    return $this->action;
  }
  public function view()
  {
    return $this->view;
  }
   //Setters
  public function setModule($module)
  {
    if (!is_string($module) || empty($module))
    {
      throw new \InvalidArgumentException('Le module doit être une chaine de caractères valide');
    }
     $this->module = $module;
  }
   public function setAction($action)
  {
    if (!is_string($action) || empty($action))
    {
      throw new \InvalidArgumentException('L\'action doit être une chaine de caractères valide');
    }
     $this->action = $action;
  }
   public function setView($view)
  {
    if (!is_string($view) || empty($view))
    {
      throw new \InvalidArgumentException('La vue doit être une chaine de caractères valide');
    }
     $this->view = $view;
     //Passe le fichier de la vue à la page
    $this->page->setContentFile(__DIR__.'/../../App/'.$this->app->name().'/Modules/'.$this->module.'/Views/'.$this->view.'.php');
  }
}