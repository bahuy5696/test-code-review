<?php

namespace App\Storage;

use App\Model;
use Doctrine\ORM\EntityManager;

class DataStorage
{

  /**
   * @var EntityManager
   */
  private $em;

  public function __construct(EntityManager $em)
  {
    $this->em = $em;
  }

  /**
   * @param int $projectId
   * @return Model\Project
   * @throws Model\NotFoundException
   */
  public function getProjectById($projectId)
  {
    $project = $this->em->find(Model\Project::class, $projectId);
    if (!$project) {
      throw new Model\NotFoundException();
    }

    return $project;
  }

  /**
   * @param int $project_id
   * @param int $limit
   * @param int $offset
   * @return array
   */
  public function getTasksByProjectId(int $project_id, $limit, $offset): array
  {
    $limit = is_numeric($limit) ? (int)$limit : 10;
    $offset = is_numeric($offset) ? (int)$offset : 0;

    $tasks = $this->em->getRepository(Model\Task::class)->findBy(
      ['project_id' => $project_id],
      ['id' => 'ASC'],
      max(1, $limit),
      max(0, $offset)
    );

    return array_map(function ($task) {
      return $task->jsonSerialize();
    }, $tasks);
  }

  /**
   * @param array $data
   * @param int $projectId
   * @return Model\Task
   */
  public function createTask(array $data, $projectId): Model\Task
  {
    $task = new Model\Task();

    $task->setProjectId((int)$projectId);
    $task->setTitle($data['title'] ?? '');
    $task->setStatus($data['status'] ?? 'PENDING');

    $this->em->persist($task);
    $this->em->flush();

    return $task;
  }
}
