<?php

namespace UserBundle\Controller;

use CMEN\GoogleChartsBundle\GoogleCharts\Charts\PieChart;
use FriendBundle\Entity\Comment;
use FriendBundle\Entity\Event;
use FriendBundle\Entity\Paiement;
use FriendBundle\Entity\Participant;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function adminAction(Request $request)
    {

        return $this->render('@User/Admin/base2.html.twig');
    }

    public function afficherAction(Request $request)
    {

        $em=$this->getDoctrine()->getManager();
        $event=$em->getRepository(Event::class)->findAll();
        $comment=$em->getRepository (Comment::class)->findAll ();
        $pay=$em->getRepository (Paiement::class)->findAll ();
        return $this->render('@User/Admin/table.html.twig', array(
            "event" =>$event,"comment"=>$comment,"pay"=>$pay
        ));

    }

    public function afficherPDFAction(Request $request)
    {
        $snappy = $this->get ('knp_snappy.pdf');

        $em=$this->getDoctrine()->getManager();
        $event=$em->getRepository(Event::class)->findAll();
        $comment=$em->getRepository (Comment::class)->findAll ();


        $html = $this->renderView ('@User/Admin/PDF.html.twig', array(
            "event" =>$event,
            "comment"=>$comment
        ));
        $filename = 'myFirstSnappyPDF';
        return new Response(
            $snappy->getOutputFromHtml($html),
            200,
            array(
                'Content-Type'          => 'application/pdf',
                'Content-Disposition'   => 'inline; filename="'.$filename.'.pdf"'
            )
        );

    }

    public function deleteAction(Request $request){

        $id=$request->get('id');
        $event=$this->getDoctrine()
            ->getRepository(Event::class)
            ->find($id);

        $em=$this->getDoctrine()->getManager();
        $em->remove($event);
        $comment=$this->getDoctrine()
            ->getRepository(Comment::class)
            ->findCommentById($id);
        foreach ($comment as $c)
        {
            $em->remove($c);
        }

        $participant=$this->getDoctrine()
            ->getRepository(Participant::class)
            ->findParticipant($id);
        foreach ($participant as $p)
        {
            $em->remove($p);
        }

        $em->flush();
        return $this->redirectToRoute('table_dashboard');

    }

    public function deleteCommentAction(Request $request)
    {

        $id = $request->get('id');
        $em= $this->getDoctrine()->getManager();
        $comment=$em->getRepository(Comment::class)->find($id);
        $em->remove($comment);
        $em->flush();
        return $this->redirectToRoute('table_dashboard');
    }

    public function deletepaiementAction(Request $request)
    {

        $id = $request->get('id');
        $em= $this->getDoctrine()->getManager();
        $comment=$em->getRepository(Paiement::class)->find($id);
        $em->remove($comment);
        $em->flush();
        return $this->redirectToRoute('table_dashboard');
    }


    public function googleAction(){

        $pieChart = new PieChart();
        $em= $this->getDoctrine();
        $classes = $em->getRepository(Event::class)->findAll();
        $totalEtudiant=0;



        foreach($classes as $classe) {
            $totalEtudiant=$totalEtudiant+$classe->getNbrVue();
            }
            $data= array();
            $stat=['event', 'nbrVue'];
            $nb=0;
            array_push($data,$stat);
            foreach($classes as $classe) {
                $stat=array();
                array_push($stat,$classe->getTitre(),(($classe->getNbrVue()) *100)/$totalEtudiant);
                $nb=($classe->getNbrVue() *100)/$totalEtudiant;
                $stat=[$classe->getTitre(),$nb];
                array_push($data,$stat);
            }
            $pieChart->getData()->setArrayToDataTable(
                $data
            );
            $pieChart->getOptions()->setTitle('Pourcentages des vues par event');
            $pieChart->getOptions()->setHeight(500);
            $pieChart->getOptions()->setWidth(900);
            $pieChart->getOptions()->getTitleTextStyle()->setBold(true);
            $pieChart->getOptions()->getTitleTextStyle()->setColor('#009900');
            $pieChart->getOptions()->getTitleTextStyle()->setItalic(true);
            $pieChart->getOptions()->getTitleTextStyle()->setFontName('Arial');
            $pieChart->getOptions()->getTitleTextStyle()->setFontSize(20);
            return $this->render('@User/Admin/google.html.twig', array('piechart' => $pieChart));

                }


}
