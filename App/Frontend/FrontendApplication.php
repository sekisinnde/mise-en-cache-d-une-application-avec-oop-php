<?php
namespace App\Frontend;
 use \OCFram\Application;
 class FrontendApplication extends Application
{
  public function __construct()
  {
    parent::__construct();
     $this->name = 'Frontend';
  }
   public function createCache()
   {
    return array('index');// Tableau contenant les vues à mettre en cache dans cette application (utilisé dans Page::getGeneratedPage()). Ici uniquement 'index.php'.
   }
   public function run()
  {
    $this->setController($this->getController());//on connait le module, l'action (donc la vue) et les données en $_GET (id) via le controleur qui est ici enregistré dans un atttribut dédié ($controller) pour être accessible dans les ApplicationComponent $cache et $page
     //si la vue corespondant à l'action n'est pas en cache ou si elle a expirée
    if(!$viewString = $this->cache->readView($this->name. '_'.$this->controller->module(). '_'.$this->controller->view()))
     {
      $this->controller->execute();//Execution du controller qui passe les donnees pour la vue a la page
      $pageString = $this->controller->page()->getGeneratedPage();//Page générée par le controller (en dehors de httpResponse)
     }
    else //Si la vue existe en cache
     {
      //Affichage de la vue en cache par la page
      $pageString = $this->controller->page()->getCachePage($viewString);
     }
    
    //Passage de la page a httpResponse et envoi au client
    $this->httpResponse->setPage($pageString);
    $this->httpResponse->send();
  }
}