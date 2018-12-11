<?php
namespace App\Frontend\Modules\News;
 use \OCFram\BackController;
use \OCFram\HTTPRequest;
use \Entity\Comment;
use \FormBuilder\CommentFormBuilder;
use \OCFram\FormHandler;
 class NewsController extends BackController
{
  public function executeIndex(HTTPRequest $request)
  {
    //Recuperation des parametres de l'application
    $nombreNews = $this->app->config()->get('nombre_news');
    $nombreCaracteres = $this->app->config()->get('nombre_caracteres');
    
    //On definie le titre de la page
    $this->page->addVar('title', 'Liste des '.$nombreNews.' dernières news');
    
    //On récupère la liste de News (newsManager instancié dans lib/OCFram/BackController)
    $listeNews = $this->newsManager->getList(0, $nombreNews);
    
    //Limitation du nbre de caracteres du contenu a $nombreCaracteres caracteres
    foreach ($listeNews as $news)
    {
      if (strlen($news->contenu()) > $nombreCaracteres)
      {
        $debut = substr($news->contenu(), 0, $nombreCaracteres);
        $debut = substr($debut, 0, strrpos($debut, ' ')) . '...';
        
        $news->setContenu($debut);
      }
    }
    
    // On passe la variable $listeNews à la vue via la page
    $this->page->addVar('listeNews', $listeNews);
  }
  
  public function executeShow(HTTPRequest $request)
  {
    //Si la news n'existe pas en cache ou y a expiré
    if(!$news = $this->app()->cache()->readData('news-'. $request->getData('id')))
    {
      $news = $this->newsManager->getUnique($request->getData('id'));//On execute la requete SQL
      
      if (!empty($news))//si la requete n'est pas vide
      {
        //On enregistre la news en cache ('news-id')
        $this->app()->cache()->writeData('news-'. $request->getData('id'), $news);
      }
    }
     //Si la liste de commentaires n'existe pas en cache ou y a expiré
    if(!$comments = $this->app()->cache()->readData('comments-'. $request->getData('id')))
    {
      $comments = $this->commentsManager->getListOf($request->getData('id'));//On execute la requete SQL
       if (!empty($comments))//si la requete n'est pas vide
      {
        //On enregistre la liste de commentaires ('commentaires-id') 
        $this->app()->cache()->writeData('comments-'. $request->getData('id'), $comments);
      }
    }
    
    if (empty($news))
    {
      $this->app->httpResponse()->redirect404();
    }
    
    $this->page->addVar('title', $news->titre());
    $this->page->addVar('news', $news);
    $this->page->addVar('comments', $comments);
  }
   public function executeInsertComment(HTTPRequest $request)
  {
    // Si le formulaire a été envoyé.
    if ($request->method() == 'POST')
    {
      $comment = new Comment([
        'news' => $request->getData('news'),
        'auteur' => $request->postData('auteur'),
        'contenu' => $request->postData('contenu')
      ]);
    } else {
      $comment = new Comment;
    }
     $formBuilder = new CommentFormBuilder($comment);
    $formBuilder->build();
     $form = $formBuilder->form();
     $formHandler = new FormHandler($form, $this->commentsManager, $request);
     if ($formHandler->process())
    {
      $this->app->user()->setFlash('Le commentaire a bien été ajouté, merci !');
      
      $this->app->httpResponse()->redirect('news-'.$request->getData('news').'.html');
    }
     $this->page->addVar('comment', $comment);
    $this->page->addVar('form', $form->createView());
    $this->page->addVar('title', 'Ajout d\'un commentaire');
  }
}