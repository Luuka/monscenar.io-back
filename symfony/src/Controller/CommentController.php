<?php

namespace App\Controller;

use App\Entity\Comment;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Project;
use App\Entity\Sequence;
use App\Entity\Version;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Constraints\Date;

class CommentController extends AbstractController
{
    /**
     * @Route("project/{id}/version/{versionId}/comments", name="getComments", methods="GET")
     * @param Request $request
     * @param Project $project
     * @param Version $version
     * @return JsonResponse
     */
    public function getCommentsAction(Request $request, Project $project, Version $version)
    {
        $this->denyAccessUnlessGranted('edit', $project);

        $commentRepository = $this->getDoctrine()->getRepository(Comment::class);
        $comments = $commentRepository->findBy(
            ['version' => $version]
        );

        $data = [];
        /** @var Comment $comment */
        foreach ($comments as $comment) {
            $json = $this->serializerService->serialize(
                $comment,
                [AbstractNormalizer::IGNORED_ATTRIBUTES => Sequence::IGNORED_ATTRIBUTES]
            );

            if(!isset($data[$comment->getBlockIndex()])) {
                $data[$comment->getBlockIndex()] = [];
            }

            $data[$comment->getBlockIndex()][] = json_decode($json);
        }

        return new JsonResponse(json_encode($data, JSON_FORCE_OBJECT), JsonResponse::HTTP_OK, [], true);
    }

    /**
     * @Route("project/{id}/version/{versionId}/comment", name="createComment", methods="POST")
     * @param Request $request
     * @param Project $project
     * @param Version $version
     * @return JsonResponse
     */
    public function createCommentAction(Request $request, Project $project, Version $version)
    {
        $this->denyAccessUnlessGranted('edit', $project);

        /** @var Comment $newComment */
        $newComment = $this->serializerService->deserialize($request->getContent(), Comment::class);
        $newComment->setProject($project);
        $newComment->setVersion($version);
        $newComment->setCreatedAt(new \DateTime());

        $em = $this->getDoctrine()->getManager();
        $em->persist($newComment);
        $em->flush();

        $json = $this->serializerService->serialize(
            $newComment,
            [AbstractNormalizer::IGNORED_ATTRIBUTES => Comment::IGNORED_ATTRIBUTES]
        );

        return new JsonResponse($json, JsonResponse::HTTP_OK, [], true);
    }

    /**
     * @Route("project/{id}/version/{versionId}/comment/{commentId}", name="deleteComment", methods="DELETE")
     * @param Request $request
     * @param Project $project
     * @param Version $version
     * @param int $commentId
     * @return JsonResponse
     */
    public function deleteAction(Request $request, Project $project, Version $version, int $commentId)
    {
        $em = $this->getDoctrine()->getManager();

        $comment = $em->getRepository(Comment::class)->find($commentId);

        $em->remove($comment);
        $em->flush();

        return new JsonResponse([], JsonResponse::HTTP_OK, []);
    }
}