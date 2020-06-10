<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Doctrine\ORM\EntityManagerInterface;


use App\Entity\Complaint;
use App\Entity\User;
use App\Entity\ComplaintType;
use App\Entity\Photo;

use Dompdf\Dompdf;
use Dompdf\Options;

use Twig\Extra\Intl\IntlExtension;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
    public function list($return_array = false)
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

        if($return_array){
            return $complaints_data;
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

    /**
    * @Route("/complaints/pdf", name="api_complaints_pdf")
    */
    public function reportPdf()
    {
        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        
        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);
        
        $complaints = $this->list(true);

        $complaints = array('complaints'=>$complaints);
        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('complaint/complaint.html.twig', $complaints);
        
        // Load HTML to Dompdf
        $dompdf->loadHtml($html);
        
        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (inline view)
        $dompdf->stream("mypdf.pdf", [
            "Attachment" => false
        ]);
    }

    /**
    * @Route("/complaints/xls", name="api_complaints_xls")
    */
    public function reportXls()
    {
        $spreadsheet = new Spreadsheet();
        
        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Código');
        $sheet->setCellValue('B1', 'Tipo');
        $sheet->setCellValue('C1', 'Asunto');
        $sheet->setCellValue('D1', 'Usuario');

        $sheet->setTitle("Complaints");
        
        // Crear tu archivo Office 2007 Excel (XLSX Formato)
        $writer = new Xlsx($spreadsheet);
        
        $complaints = $this->list(true);


        $cell = 3;
        foreach ($complaints as $key => $complaint) {
            
            $sheet->setCellValue('A'.$cell, $complaint['id']);
            $sheet->setCellValue('B'.$cell, $complaint['type']['name']);
            $sheet->setCellValue('C'.$cell, $complaint['subject']);
            $sheet->setCellValue('D'.$cell, $complaint['user']['email']);

            $cell++;
        }

        // Crear archivo temporal en el sistema
        $fileName = 'excel_complaints.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        
        // Guardar el archivo de excel en el directorio temporal del sistema
        $writer->save($temp_file);
        
        // Retornar excel como descarga
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
    }
}
