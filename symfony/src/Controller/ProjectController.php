<?php


namespace App\Controller;


use App\Entity\Project;
use App\Entity\Sequence;
use App\Entity\User;
use App\Security\Voter\ProjectVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class ProjectController extends AbstractController
{
    /**
     * @Route("/project", name="getProjects", methods="GET")
     */
    public function getProjectsAction(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $projects = $user->getProjects();

        $data = $this->serializerService->serialize($projects, [AbstractNormalizer::IGNORED_ATTRIBUTES => Project::IGNORED_ATTRIBUTES]);

        return new JsonResponse($data, JsonResponse::HTTP_OK, [], true);
    }

    /**
     * @Route("/project/{id}", name="getProject", methods="GET")
     * @param Request $request
     * @param Project $project
     * @return JsonResponse
     */
    public function getProjectAction(Request $request, Project $project): JsonResponse
    {
        $this->denyAccessUnlessGranted(ProjectVoter::EDIT, $project);

        $data = $this->serializerService->serialize(
            $project,
            [AbstractNormalizer::IGNORED_ATTRIBUTES => Project::IGNORED_ATTRIBUTES]
        );

        return new JsonResponse($data, JsonResponse::HTTP_OK, [], true);
    }

    /**
     * @Route("/project/", name="createProject", methods="POST")
     * @param Request $request
     * @return JsonResponse
     */
    public function createProjectAction(Request $request)
    {
        $content = $request->getContent();

        /** @var Project $project */
        $project = $this->serializerService->deserialize($content, Project::class);

        $project->setOwner($this->getUser());

        $this->entityManager->persist($project);
        $this->entityManager->flush();

        $data = $this->serializerService->serialize(
            $project,
            [AbstractNormalizer::IGNORED_ATTRIBUTES => Project::IGNORED_ATTRIBUTES]
        );

        return new JsonResponse($data, JsonResponse::HTTP_CREATED, [], true);
    }

    /**
     * @Route("/project/{id}", name="updateProject", methods="PUT")
     * @param Request $request
     * @param Project $projectToUpdate
     * @return JsonResponse
     */
    public function updateProjectAction(Request $request, Project $projectToUpdate)
    {
        $this->denyAccessUnlessGranted(ProjectVoter::EDIT, $projectToUpdate);

        $content = $request->getContent();

        /** @var Project $project */
        $project = $this->serializerService->deserialize($content, Project::class);

        $projectToUpdate->setName($project->getName());

        $this->entityManager->flush();

        $data = $this->serializerService->serialize(
            $projectToUpdate,
            [AbstractNormalizer::IGNORED_ATTRIBUTES => Project::IGNORED_ATTRIBUTES]
        );

        return new JsonResponse($data, JsonResponse::HTTP_OK, [], true);
    }

    /**
     * @Route("/project/{id}", name="deleteProject", methods="DELETE")
     * @param Request $request
     * @param Project $projectToDelete
     * @return JsonResponse
     */
    public function deleteProjectAction(Request $request, Project $projectToDelete)
    {
        $this->denyAccessUnlessGranted(ProjectVoter::EDIT, $projectToDelete);

        $this->entityManager->remove($projectToDelete);
        $this->entityManager->flush();

        return new JsonResponse([], JsonResponse::HTTP_OK, []);
    }

    /**
     * @Route("/project/{id}/sequence", name="getProjectSequences", methods="GET")
     * @param Request $request
     * @param Project $project
     * @return JsonResponse
     */
    public function getProjectSequencesAction(Request $request, Project $project): JsonResponse
    {
        $this->denyAccessUnlessGranted(ProjectVoter::EDIT, $project);

        $data = $this->serializerService->serialize(
            $project->getSequences(),
            [AbstractNormalizer::IGNORED_ATTRIBUTES => Sequence::IGNORED_ATTRIBUTES]
        );

        return new JsonResponse($data, JsonResponse::HTTP_OK, [], true);
    }
}