<?php
namespace Model;
 use \Entity\News;
 class NewsManagerPDO extends NewsManager
{
  protected function add(News $news)
  {
    $requete = $this->dao->prepare('INSERT INTO news SET auteur = :auteur, titre = :titre, contenu = :contenu, dateAjout = NOW(), dateModif = NOW()');
    
    $requete->bindValue(':titre', $news->titre());
    $requete->bindValue(':auteur', $news->auteur());
    $requete->bindValue(':contenu', $news->contenu());
    
    $requete->execute();
     $NewsId = $this->dao->lastInsertId();//on récupère l'id de la News ajoutée
     //On indique à l'Observer Cache qu'une news d'id $NewsId a été ajoutée
    $this->setActionValBuffer([
      'action' => 'add',
      'val' => $NewsId]);
    $this->notify();
  }
   public function count()
  {
    return $this->dao->query('SELECT COUNT(*) FROM news')->fetchColumn();
  }
   public function delete($id)
  {
    $this->dao->exec('DELETE FROM news WHERE id = '.(int) $id);
     //On indique à l'Observer Cache qu'une news d'id $id a été supprimée
    $this->setActionValBuffer([
      'action' => 'delete',
      'val' => $id]);
    $this->notify();
  }
   public function getList($debut = -1, $limite = -1)
  {
    $sql = 'SELECT id, auteur, titre, contenu, dateAjout, dateModif FROM news ORDER BY id DESC';
    
    if ($debut != -1 || $limite != -1)
    {
      $sql .= ' LIMIT '.(int) $limite.' OFFSET '.(int) $debut;
    }
    
    $requete = $this->dao->query($sql);
    $requete->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, '\Entity\News'); //Parametrage afin que les resultats de la requete soient recuperes en tant qu'instances de la classe News
    
    $listeNews = $requete->fetchAll();
    
    foreach ($listeNews as $news)
    {
      $news->setDateAjout(new \DateTime($news->dateAjout()));
      $news->setDateModif(new \DateTime($news->dateModif()));
    }
    
    $requete->closeCursor();
    
    return $listeNews;
  }
  
  public function getUnique($id)
  {
    $requete = $this->dao->prepare('SELECT id, auteur, titre, contenu, dateAjout, dateModif FROM news WHERE id = :id');
    $requete->bindValue(':id', (int) $id, \PDO::PARAM_INT);
    $requete->execute();
    
    $requete->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, '\Entity\News');
    
    if ($news = $requete->fetch())
    {
      $news->setDateAjout(new \DateTime($news->dateAjout()));
      $news->setDateModif(new \DateTime($news->dateModif()));
      
      return $news;
    }
    
    return null;
  }
   protected function modify(News $news)
  {
    $requete = $this->dao->prepare('UPDATE news SET auteur = :auteur, titre = :titre, contenu = :contenu, dateModif = NOW() WHERE id = :id');
    
    $requete->bindValue(':titre', $news->titre());
    $requete->bindValue(':auteur', $news->auteur());
    $requete->bindValue(':contenu', $news->contenu());
    $requete->bindValue(':id', $news->id(), \PDO::PARAM_INT);
    
    $requete->execute();
     //On indique à l'Observer Cache qu'une news d'id $news->id() a été modifiée
    $this->setActionValBuffer([
      'action' => 'modify',
      'val' => $news->id()]);
    $this->notify();
  }
}