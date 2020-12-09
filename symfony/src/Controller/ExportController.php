<?php


namespace App\Controller;

use App\Export\Html2Pdf;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class ExportController extends AbstractController
{
    /**
     * @Route("/export/pdf", name="exportToPDF", methods="POST")
     * @param Request $request
     * @return Response
     * @throws \Spipu\Html2Pdf\Exception\Html2PdfException
     */
    public function exportToPDFAction(Request $request): Response
    {
        $tokens = json_decode($request->getContent(), true)['tokens'];
        $titlePage = json_decode($request->getContent(), true)['titlePage'];
        $title = json_decode($request->getContent(), true)['title'];

        $content = $this->renderView('script.html.twig', [
            'title_page' => $titlePage,
            'tokens' => $tokens
        ]);

        $filename = $title.'.pdf';

        $html2pdf = new Html2Pdf("p","A4","fr");
        $html2pdf->setDefaultFont('courier');
        $html2pdf->writeHTML($content);
        $pdfContent = $html2pdf->output($filename, 'S');

        $headers = [
            'Content-Description' => 'File Transfer',
            'Content-Disposition' => 'attachment; filename="'.$title.'.pdf"',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Type' => 'application/pdf'
        ];

        //return new Response($content);
        return new Response($pdfContent, Response::HTTP_OK, $headers);
    }

}