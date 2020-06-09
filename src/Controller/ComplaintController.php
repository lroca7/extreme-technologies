<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use Doctrine\ORM\EntityManagerInterface;


use App\Entity\Complaint;
use App\Entity\User;
use App\Entity\ComplaintType;
use App\Entity\Photo;

class ComplaintController extends AbstractController
{
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
    * @Route("/api/complaints", name="api_create_complaint", methods="POST")
    */
    public function new(EntityManagerInterface $em, Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $repoUser = $em->getRepository(User::class);
        $repoType = $em->getRepository(ComplaintType::class);

        $complaint = new Complaint();

        if (isset($data['subject'])) {
            $complaint->setSubject($data['subject']);
        }

        if (isset($data['user'])) {
            $user = $repoUser->find($data['user']['id']);
            $complaint->setUser($user);
        }
            
        if (isset($data['type'])) {
            $type = $repoType->find($data['type']['id']);
            $complaint->setType($type);
        }

        if(isset($data['photo'])){

            $photo = new Photo();

            $photo->setOriginalName($data['photo']['name']);
            $photo->setMimeType($data['photo']['mimeType']);
            $photo->setImageBase64($data['photo']['imageBase64']);

            $em->persist($photo);

            $complaint->setPhoto($photo);
        }

        
        $em->persist($complaint);
        $em->flush();

        $complaint = array(
            'id' => $complaint->getId(),
            'subject' => $complaint->getSubject()
        );

        return new JsonResponse($complaint);
    }

    /**
    * @Route("/api/complaints/{id}", name="api_update_complaint", methods="PUT")
    */
    public function update(EntityManagerInterface $em, Request $request, $id)
    {
        $data = json_decode($request->getContent(), true);

        $repository = $em->getRepository(Complaint::class);
        $repoUser = $em->getRepository(User::class);
        $repoType = $em->getRepository(ComplaintType::class);

        
        $complaint = $repository->find($id);

        if ($complaint) {
            if (isset($data['subject'])) {
                $complaint->setSubject($data['subject']);
            }
    
            if (isset($data['user'])) {
                $user = $repoUser->find($data['user']['id']);
                $complaint->setUser($user);
            }
                
            if (isset($data['type'])) {
                $type = $repoType->find($data['type']['id']);
                $complaint->setType($type);
            }
    
            $em->persist($complaint);
            $em->flush();
    
            $complaint = array(
                'id' => $complaint->getId(),
                'subject' => $complaint->getSubject()
            );
    
            return new JsonResponse($complaint);
        } else {
            return $this->json([
                'error' => 'Complaint not found'
            ], 400);
        }
    }

    /**
     * @Route("/api/complaints", name="api_complaints_list")
     */
    public function list()
    {
        $em = $this->getDoctrine()->getManager();
       
        $repository = $em->getRepository(Complaint::class);
           
        $allComplaints = $repository->createQueryBuilder('p')
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->execute();

        $complaints_data = [];

        foreach ($allComplaints as $key => $complaint) {
            $complaints_data[] = array(
                    'id' => $complaint->getId(),
                    'subject' => $complaint->getSubject(),
                    'user' => [
                        'id' => $complaint->getUser()->getId(),
                        'email' => $complaint->getUser()->getEmail()
                    ],
                    'type' => [
                        'id' => $complaint->getType()->getId(),
                        'name' => $complaint->getType()->getName()
                    ]
                );
        }

        return new JsonResponse($complaints_data);

    }

    /**
    * @Route("/api/complaints/{id}", name="api_complaints_show", methods={"GET"})
    */
    public function show(EntityManagerInterface $em, $id)
    {
        $repository = $em->getRepository(Complaint::class);

        $complaint = $repository->find($id);

        if (!$complaint) {
            throw $this->createNotFoundException(sprintf('Complaint not found'));
        } else {
            $complaint_data = array(
                'id' => $complaint->getId(),
                'subject' => $complaint->getSubject(),
                'user' => [
                    'id' => $complaint->getUser()->getId(),
                    'email' => $complaint->getUser()->getEmail()
                ],
                'type' => [
                    'id' => $complaint->getType()->getId(),
                    'name' => $complaint->getType()->getName()
                ]
                );

            if($complaint->getPhoto()){
                $photo = array(
                    'name' => $complaint->getPhoto()->getOriginalName(),
                    'mimeType'=> $complaint->getPhoto()->getMimeType(),
                    'imageBase64' => $complaint->getPhoto()->getImageBase64()
                );
                $complaint_data['photo'] = $photo;
                
            }else{
                $complaint_data['photo'] = null;
            }
        }
        
        return new JsonResponse($complaint_data);
    }
}
