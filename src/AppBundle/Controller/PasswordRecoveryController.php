<?php
namespace AppBundle\Controller;


use AppBundle\Entity\Form\ResetPasswordType;
use AppBundle\Entity\PasswordRecover;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Tests\Extension\Core\Type\PasswordTypeTest;
use Symfony\Component\Form\Tests\Extension\Core\Type\RepeatedTypeTest;
use Symfony\Component\Form\Tests\Extension\Core\Type\SubmitTypeTest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Person;
use AppBundle\Entity\DigestEntry;
use AppBundle\Entity\Form\PersonType;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/*
 * TODO:
 * - vertalingen implementeren
 */

class PasswordRecoveryController extends Controller
{
    /**
     * @Route("/paswoord/recover/", name="request_recover")
    */
    public function requestReset(Request $request){ // 1. build form and submit
        $form = $this->createFormBuilder()
            ->add('logincredential', TextType::class,array(
                'label' => 'Telefoon of email-adres',
                'invalid_message' => 'vul een geldig email of telefoonnummer in!',
                'required' => true))
            ->add('save', SubmitType::class, array('label' => 'Versturen'))
            ->getForm();

        $form->handleRequest($request);

        $em = $this->getDoctrine()->getEntityManager();

        if($form->isSubmitted() && $form->isValid()){

            $loginCredential = $form['logincredential']->getData();

            $qb = $em->createQueryBuilder();
            $qb ->select('person')
                ->from('AppBundle:Person', 'person')
                ->where('person.email = ?1')
                ->orWhere('person.telephone = ?2')
                ->setParameter(1,$loginCredential)
                ->setParameter(2,$loginCredential);

            $user = $qb->getQuery()->getResult();
            $user = array_pop($user);


            if(!is_null($user)){
                $test = $em->createQueryBuilder();
                $test->select('recover.id')
                    ->from('AppBundle:PasswordRecover','recover')
                    ->where('recover.person = ?1')
                    ->setParameter(1, $user->getId());


                $requested = $test->getQuery()->getResult();
                $requested = array_pop($requested);

                if(empty($requested)) {
                    $this->recoverAction($user);
                    $this->addFlash('success', 'Er werd een mail gestuurd naar het ingevulde adres. Volg de link in de mail om uw paswoord te resetten');
                }else{
                    $this->addFlash('error',"deze gebruikersnaam werd al reeds opgegeven voor een paswoord reset");
                }
            }else{
                $this->addFlash('error', 'dit emailadres of telefoonnummer werd niet gevonden.');
            }
            return $this->render('passwordrecovery/password_recovery_submit_status.html.twig');//submitted; show status
        }

        $this->addFlash('status',"vul uw email of telefoonnummer in. Dan sturen wij u een mail waar u uw paswoord kan veranderen.");
        return $this->render('passwordrecovery/recover_form.html.twig', array(//show recover form
            'form' => $form->createView(),
        ));
    }

    //2. GETS CALLED BY REQUEST AND INSERTS INTO DB AND SENDS MAIL
    public function recoverAction(Person $user)
    {
        $reset = new PasswordRecover($user);
        $em = $this->getDoctrine()->getManager();
        $em->persist($reset);
        $em->flush();
        $message = \Swift_Message::newInstance()
            ->setSubject('roeselarevrijwilligt.be wachtwoord reset')
            ->setFrom('reset@roeselarevrijwilligt.be')
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView(
                    'passwordrecovery/reset_email.html.twig',
                    array('user' => $reset)
                ),
                'text/plain'
            );
        $this->get('mailer')->send($message);
    }


    // 3. HANDLES THE VALIDATION OG HASH URL AND SHOWS FORM PASSWD CHANGE
    /**
     * @Route("/paswoord/recover/{hash}", name="password_recover")
     */
    public function resetForm($hash, Request $request){
        $em = $this->getDoctrine()->getEntityManager();

        $recovery = $em->getRepository("AppBundle:PasswordRecover")
                       ->findOneBy(array('hash' => $hash));


        if(!is_null($recovery)){
            if(strtotime($recovery->getExpiryDate()) >= strtotime(date("Y-m-d H:i:s"))){

                $person  = $em->getRepository("AppBundle:Person")
                    ->find($recovery->getPerson()->getId());

                $form = $this->createFormBuilder()
                             ->add('newPassword', RepeatedType::class, array(
                                   'type' => PasswordType::class,
                                   'invalid_message' => 'De paswoorden moeten overeen komen!.',
                                   'required' => true,
                                   'first_options'  => array('label' => 'Nieuw wachtwoord'),
                                   'second_options' => array('label' => 'Herhaal wachtwoord'),
                                  )
                                )
                             ->add('submit', SubmitType::class)
                             ->getForm();

                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()){
                    $len = strlen($form->get('newPassword')->getData());
                    if($len <= 4096 && $len >= 8) {// moet uit entity constraint gehaald worden, hoe doe ik dit help?
                        $person->setPlainPassword($form->get('newPassword')->getData());
                        $password = $this->get("security.password_encoder")
                            ->encodePassword($person, $person->getPlainPassword());
                        $person->setPassword($password);

                        $em->persist($person);//persist new password
                        $em->remove($recovery);//remove recovery from database
                        $em->flush();

                        $this->addFlash('success', "uw paswoord werd succesvol aangepast! U kan nu inloggen met uw nieuw paswoord.");
                        return $this->render('passwordrecovery/password_recovery_submit_status.html.twig');
                    }
                    else {
                        $this->addFlash('error', "Uw nieuw wachtwoord moet tussen de 8 en 4096 karakters bevatten!");
                        return $this->render('passwordrecovery/recover_form.html.twig', array(
                            'form' => $form->createView(),
                        ));
                    }
                }
                return $this->render('passwordrecovery/recover_form.html.twig', array(
                    'form' => $form->createView(),
                ));

            }
            else{
                $this->addFlash('error', 'vraag een nieuwe link aan, deze is vervallen!');
                return $this->render('passwordrecovery/password_recovery_submit_status.html.twig');
            }
        }
        else{
            $this->addFlash('error', "Deze link is niet valid! Ofwel is deze al gebruikt geweest ofwel is deze gewoon niet gelidg!");
            return $this->render('passwordrecovery/password_recovery_submit_status.html.twig');

        }
    }
}

