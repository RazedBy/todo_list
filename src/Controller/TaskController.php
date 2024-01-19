<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class TaskController extends AbstractController
{
    #[Route('/task', name: 'app_task')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/TaskController.php',
        ]);
    }

    #[Route('/api/tasks', name: 'addTask', methods: ["POST"])]
    public function createTask(Request $request, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse
    {
        // Désérialiser les données JSON en objet Task
       try{ $task = $serializer->deserialize($request->getContent(), Task::class, 'json');

        // Enregistrer la tâche dans la base de données
        $em->persist($task);
        $em->flush();

        // Sérialiser la tâche créée pour la réponse JSON
        $jsonTask = $serializer->serialize($task, 'json',['json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS]);

        // Retourner une réponse JSON avec la tâche créée et le statut HTTP 201 (Created)
        return new JsonResponse($jsonTask, Response::HTTP_CREATED);
        }catch (\Exception $e){
           return new JsonResponse($e->getMessage(), JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    #[Route('/api/tasks/{id}', name: 'getTask', methods: ["GET"])]
    public function getTask(Task $task, SerializerInterface $serializer): JsonResponse
    {
        // Utilisation du Serializer pour sérialiser l'entité Task
        try{
            $jsonTask = $serializer->serialize($task, 'json',['json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS]);
            return new JsonResponse($jsonTask, JsonResponse::HTTP_OK,[], true);

        }catch (\Exception $e){
            return new JsonResponse($e->getMessage(), JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Retourner une réponse JSON avec la tâche sérialisée
    }

    #[Route('/api/tasks/', name: 'getTasks', methods: ["GET"])]
    public function getTasks(TaskRepository $taskRepository, SerializerInterface $serializer): JsonResponse
    {
        //Nous récupérons toutes les tâches stockés dans la base de données
        $taskList = $taskRepository->findAll();
        // Utilisation du Serializer pour sérialiser l'entité Task
        $jsonTaskList = $serializer->serialize($taskList, 'json',['json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS]);

        // Retourner une réponse JSON avec la tâche sérialisée
        return new JsonResponse($jsonTaskList, JsonResponse::HTTP_OK,[], true);
    }

    #[Route('/api/tasks/{id}', name: 'updateTask', methods: ["PUT"])]
    public function updateTask(Request $request, TaskRepository $taskRepository, Task $currentTask,SerializerInterface $serializer, int $id,EntityManagerInterface $em): JsonResponse
    {
        // Récupérer la tâche à mettre à jour depuis le TaskRepository
        $updatedBook = $serializer->deserialize($request->getContent(), Task::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentTask]);

        //Enregistrer l'update de la tâche dans la base de données
        $em->persist($updatedBook);
        $em->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    #[Route('/api/tasks/{id}',name : 'deleteTask', methods: ['DELETE'] )]
    public function deleteBook(Task $task, EntityManagerInterface $em) : JsonResponse
    {
        $em ->remove($task);
        $em-> flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }


}
