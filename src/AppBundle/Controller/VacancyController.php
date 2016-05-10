<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Vacancy;
use AppBundle\Entity\Candidacy;
use AppBundle\Entity\Form\VacancyType;

class VacancyController extends controller
{
    /**
     * @Route("/vacature/pdf/{urlid}", name="vacancy_pdf_by_urlid")
     */
    public function createPdfAction($title)
    {
        $em = $this->getDoctrine()->getManager();
        $vacancy = $em->getRepository("AppBundle:Vacancy")->findOneByUrlId($title);
        if ($vacancy) {
            $pdf = new \FPDF_FPDF("P", "pt", "A4");
            $pdf->AddPage();
            $pdf->SetFont("Times", "B", 12);
            $pdf->Cell(0, 10, $vacancy->getTitle(), 0, 2, "C");
            $pdf->MultiCell(0, 20, "Beschrijving: \t".
                $vacancy->getDescription());
            $pdf->MultiCell(0, 20, "Organisatie: \t".
                $vacancy->getOrganisation()->getStreet(), 0, "L");
            $pdf->MultiCell(0, 20, "Locatie: \t", 0, "L");
            $pdf->Output();
            return $this->render($pdf->Output());
        } else
            throw new \Exception("De gevraagde vacature bestaat niet!");
    }

    /**
     * @Security("has_role('ROLE_USER')") //TODO: apply correct role
     * @Route("/vacature/nieuw", name="create_vacancy")
     */
    public function createVacancyAction(Request $request)
    {
        $vacancy = new Vacancy();
        $vacancy->setStartdate(new \DateTime("today"))
            ->setEnddate(new \DateTime("today"));
        $form = $this->createForm(VacancyType::class, $vacancy);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            // $user = $this->get('security.token_storage')->getToken()->getUser();
            // $organisation = $user->getOrganisation();
            // $vacancy->setOrganisation($organisation);
            //
            $em->persist($vacancy);
            $em->flush();
            return $this->redirect($this->generateUrl("vacancy_by_urlid",
            ["urlid" => $vacancy->getUrlId() ] ));
        }
        return $this->render("vacancy/vacature_nieuw.html.twig",
            ["form" => $form->createView() ] );
    }

    /**
     * @Route("/vacature/{urlid}", name="vacancy_by_urlid")
     */
    public function vacancyViewAction($urlid)
    {
        $em = $this->getDoctrine()->getManager();
        $vacancy = $em->getRepository("AppBundle:Vacancy")
            ->findOneByUrlid($urlid);
        return $this->render("vacancy/vacature.html.twig",
            ["vacancy" => $vacancy]);
    }

    /**
     * @Security("has_role('ROLE_USER')")
     * @Route("/vacature/{urlid}/inschrijven", name="vacancy_subscribe")
     */
    public function subscribeVacancy($urlid)
    {
        $person = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $vacancy = $em->getRepository("AppBundle:Vacancy")
            ->findOneByUrlId($urlid);

        $candidacy = new Candidacy();
        $candidacy->setCandidate($person)->setVacancy($vacancy);

        $em->persist($candidacy);
        $em->flush();

        return $this->redirectToRoute("vacancy_by_urlid", ["urlid" => $urlid]);
    }

    public function listRecentVacanciesAction($nr)
    {
        $entities = $this->getDoctrine()
                        ->getRepository("AppBundle:Vacancy")
                        ->findBy(array(), array("id" => "DESC"), $nr);
        return $this->render("vacancy/recente_vacatures.html.twig",
            ["vacancies" => $entities]);
    }
}
