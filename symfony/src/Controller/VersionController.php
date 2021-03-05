<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Project;
use App\Entity\Sequence;
use App\Entity\Version;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class VersionController extends AbstractController
{
    /**
     * @Route("project/{id}/version/{versionIdx}", name="getVersion", methods="GET")
     * @param Request $request
     * @param Project $project
     * @param $versionIdx
     * @return JsonResponse
     */
    public function getVersionAction(Request $request, Project $project, $versionIdx)
    {
        $this->denyAccessUnlessGranted('edit', $project);

        $versionRepository = $this->getDoctrine()->getRepository(Version::class);
        $version = $versionRepository->findOneBy(
            [
                'project' => $project,
                'versionNumber' => $versionIdx
            ]
        );

        $data = $this->serializerService->serialize(
            $version,
          [AbstractNormalizer::IGNORED_ATTRIBUTES => Sequence::IGNORED_ATTRIBUTES]
        );

        return new JsonResponse($data, JsonResponse::HTTP_OK, [], true);
    }

    /**
     * @Route("project/{id}/versions", name="getVersions", methods="GET")
     * @param Request $request
     * @param Project $project
     * @return JsonResponse
     */
    public function getVersionsAction(Request $request, Project $project)
    {
        $this->denyAccessUnlessGranted('edit', $project);

        $versionRepository = $this->getDoctrine()->getRepository(Version::class);
        $versions = $versionRepository->findBy(['project' => $project]);

        $data = [];
        foreach ($versions as $version) {
            $json = $this->serializerService->serialize(
                $version,
                [AbstractNormalizer::IGNORED_ATTRIBUTES => Sequence::IGNORED_ATTRIBUTES]
            );

            $data[] = json_decode($json);
        }

        return new JsonResponse($data, JsonResponse::HTTP_OK, []);
    }

    /**
    * @Route("project/{id}/version", name="createVersion", methods="POST")
    * @param Request $request
    * @param Project $project
    * @return JsonResponse
    */
    public function createVersionAction(Request $request, Project $project)
    {
        $this->denyAccessUnlessGranted('edit', $project);

        $versionRepository = $this->getDoctrine()->getRepository(Version::class);
        $versions = $versionRepository->findBy(['project' => $project]);

        $versionsCount = count($versions);

        $version = new Version();
        $version->setVersionNumber($versionsCount+1);
        $version->setProject($project);
        $version->setFountainText($request->getContent());

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($version);
        $manager->flush();


        $versions = $versionRepository->findBy(['project' => $project]);

        $data = [];
        foreach ($versions as $version) {
            $json = $this->serializerService->serialize(
                $version,
                [AbstractNormalizer::IGNORED_ATTRIBUTES => Sequence::IGNORED_ATTRIBUTES]
            );

            $data[] = json_decode($json);
        }

        return new JsonResponse($data, JsonResponse::HTTP_OK, []);
    }
}