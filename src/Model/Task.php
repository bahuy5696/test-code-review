<?php

namespace App\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="task")
 */
class Task implements \JsonSerializable
{
  /**
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   */
  private $id;

  /**
   * @ORM\Column(type="integer")
   */
  private $project_id;

  /**
   * @ORM\Column(type="string")
   */
  private  $title;

  /**
   * @ORM\Column(type="string")
   */
  private $status;

  /**
   * @ORM\ManyToOne(targetEntity="App\Model\Project", inversedBy="tasks")
   * @ORM\JoinColumn(name="project_id", referencedColumnName="id", nullable=false)
   */
  private $project;

  /**
   * Get the value of id
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Set the value of id
   *
   * @return  self
   */
  public function setId($id)
  {
    $this->id = $id;

    return $this;
  }

  /**
   * Get the value of project_id
   */
  public function getProjectId()
  {
    return $this->project_id;
  }

  /**
   * Set the value of project_id
   *
   * @return  self
   */
  public function setProjectId($project_id)
  {
    $this->project_id = $project_id;

    return $this;
  }

  /**
   * Get the value of title
   */
  public function getTitle()
  {
    return $this->title;
  }

  /**
   * Set the value of title
   *
   * @return  self
   */
  public function setTitle($title)
  {
    $this->title = $title;

    return $this;
  }

  /**
   * Get the value of status
   */
  public function getStatus()
  {
    return $this->status;
  }

  /**
   * Set the value of status
   *
   * @return  self
   */
  public function setStatus($status)
  {
    $this->status = $status;

    return $this;
  }

  /**
   * Get the value of project
   */
  public function getProject()
  {
    return $this->project;
  }

  /**
   * Set the value of project
   *
   * @return  self
   */
  public function setProject($project)
  {
    $this->project = $project;

    return $this;
  }

  public function toArray(): array
  {
    return [
      'id'         => $this->id,
      'title'      => $this->title
    ];
  }

  public function jsonSerialize(): array
  {
    return $this->toArray();
  }
}
