<?php

namespace App\Controller;

use App\Model;
use App\Storage\DataStorage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProjectController
{
  /**
   * @var DataStorage
   */
  private $storage;

  public function __construct(DataStorage $storage)
  {
    $this->storage = $storage;
  }

  /**
   * @param Request $request
   *
   * @return JsonResponse
   * @Route("/project/{id}", name="project", methods={"GET"})
   */
  public function projectAction(Request $request): JsonResponse
  {
    try {
      $project = $this->storage->getProjectById($request->get('id'));
      return new JsonResponse($project->jsonSerialize(), 200);
    } catch (Model\NotFoundException $e) {
      return new JsonResponse(['error' => 'Not found'], 404);
    } catch (\Throwable $e) {
      return new JsonResponse(['error' => 'Something went wrong'], 500);
    }
  }

  /**
   * @param Request $request
   *
   * @return JsonResponse
   * @Route("/project/{id}/tasks", name="project-tasks", methods={"GET"})
   */
  public function projectTaskPagerAction(Request $request): JsonResponse
  {
    try {
      $tasks = $this->storage->getTasksByProjectId(
        $request->get('id'),
        $request->get('limit'),
        $request->get('offset')
      );
    } catch (\Throwable $e) {
      return new JsonResponse(['error' => 'Something went wrong'], 500);
    }

    return new JsonResponse($tasks, 200);
  }

  /**
   * @param Request $request
   *
   * @return JsonResponse
   * @Route("/project/{id}/tasks", name="project-create-task", methods={"POST"})
   */
  public function projectCreateTaskAction(Request $request): JsonResponse
  {
    try {
      $project = $this->storage->getProjectById($request->get('id'));
    } catch (Model\NotFoundException $e) {
      return new JsonResponse(['error' => 'Not found project'], 404);
    } catch (\Throwable $e) {
      return new JsonResponse(['error' => 'Something went wrong'], 500);
    }

    $payload = $request->request->all();

    //Validation
    $title = isset($payload['title']) ? trim((string) $payload['title']) : '';
    if ($title == '') {
      return new JsonResponse(['error' => "title is required"], 400);
    }

    $this->storage->createTask($payload, $project->getId());

    return new JsonResponse(
      [],
      201
    );
  }
}
