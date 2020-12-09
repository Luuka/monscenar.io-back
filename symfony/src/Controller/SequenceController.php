<?php


namespace App\Controller;

use App\Entity\Project;
use App\Entity\Sequence;
use App\Security\Voter\ProjectVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;


class SequenceController extends AbstractController
{
    /**
     * @Route("project/{id}/sequence/", name="createSequence", methods="POST")
     * @param Request $request
     * @param Project $project
     * @return JsonResponse
     */
    public function createSequenceAction(Request $request, Project $project)
    {
        $this->denyAccessUnlessGranted(ProjectVoter::EDIT, $project);

        $content = $request->getContent();

        /** @var Sequence $sequence */
        $sequence = $this->serializerService->deserialize($content, Sequence::class);
        $sequence->setProject($project);

        $this->entityManager->persist($sequence);
        $this->entityManager->flush();

        $data = $this->serializerService->serialize(
            $sequence,
            [AbstractNormalizer::IGNORED_ATTRIBUTES => Sequence::IGNORED_ATTRIBUTES]
        );

        return new JsonResponse($data, JsonResponse::HTTP_OK, [], true);
    }

    /**
     * @Route("/sequence/{id}", name="updateSequence", methods="PUT")
     * @param Request $request
     * @param Sequence $sequenceToUpdate
     * @return JsonResponse
     */
    public function updateSequenceAction(Request $request, Sequence $sequenceToUpdate)
    {
        $this->denyAccessUnlessGranted(ProjectVoter::EDIT, $sequenceToUpdate->getProject());

        $content = $request->getContent();

        /** @var Sequence $sequence */
        $sequence = $this->serializerService->deserialize($content, Sequence::class);

        $sequenceToUpdate->setFountainText($sequence->getFountainText());
        $sequenceToUpdate->setName($sequence->getName());
        $sequenceToUpdate->setOrderIndex($sequence->getOrderIndex());

        $this->entityManager->flush();

        $data = $this->serializerService->serialize(
            $sequenceToUpdate,
            [AbstractNormalizer::IGNORED_ATTRIBUTES => Sequence::IGNORED_ATTRIBUTES]
        );

        return new JsonResponse($data, JsonResponse::HTTP_OK, [], true);
    }

    /**
     * @Route("/sequence/{id}", name="deleteSequence", methods="DELETE")
     * @param Request $request
     * @param Sequence $sequenceToDelete
     * @return JsonResponse
     */
    public function deleteSequenceAction(Request $request, Sequence $sequenceToDelete)
    {
        $this->denyAccessUnlessGranted(ProjectVoter::EDIT, $sequenceToDelete->getProject());

        $this->entityManager->remove($sequenceToDelete);
        $this->entityManager->flush();

        return new JsonResponse([], JsonResponse::HTTP_OK, []);    }
}