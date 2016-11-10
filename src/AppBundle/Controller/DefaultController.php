<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use AppBundle\Entity\Person;
use AppBundle\Entity\Form\PersonType;
use AppBundle\Entity\Contact;
use AppBundle\Entity\Skillproficiency;
use AppBundle\Entity\Skill;
use AppBundle\Entity\Newslettersubscriber;
use AppBundle\Entity\Volunteer;
use AppBundle\Entity\Organisation;
use AppBundle\Entity\Vacancy;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..'),
        ]);
    }

    /**
     * @Route("/nieuwsbrief", name="newsletter")
     */
    public function newsletterAction(Request $request)
    {
        $t = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        if ($request->isMethod('POST')) {
            $newslettersubscriber = new Newslettersubscriber();
            $newslettersubscriber->setEmail( $request->request->get("mailadres") );
            $em = $this->getDoctrine()->getManager();
            $em->persist($newslettersubscriber);
            $em->flush();

            $this->addFlash('default', $t->trans('general.newsletter.ok'));
        }

        return $this->redirect($request->server->get('HTTP_REFERER'));
    }

    /**
     * @Route("/locations.json", name="api_locations")
     */
    public function locationJsonAction()
    {
        $result = array();
        $organisations = $this->getDoctrine()
            ->getRepository("AppBundle:Organisation")
            ->findBy(array(), array('id' => 'DESC'), 30);
        foreach ($organisations as $organisation) {
            if ($organisation->getLatitude()) {
                $result[] = array(
                        "lat" => $organisation->getLatitude(),
                        "long" => $organisation->getLongitude(),
                        "t" => "organisation",
                    );

            }
        }

        $vacancies = $this->getDoctrine()
            ->getRepository("AppBundle:Vacancy")
            ->findBy(array(), array('id' => 'DESC'), 30);
        foreach ($vacancies as $vacancy) {
            if ($vacancy->getLatitude()) {
                $result[] = array(
                        "lat" => $vacancy->getLatitude(),
                        "lng" => $vacancy->getLongitude(),
                        "t" => "vacancy",
                    );

            }
        }

        return new JsonResponse($result);
    }

    /**
     * @Route("/mode", name="mode_testing")
     */
    public function modeAction()
    {
        $mode = $this->container->get('kernel')->getEnvironment();
        $html = "<html><body><br />".$mode."<br /></body></html>";
        return new Response($html);
    }
}
