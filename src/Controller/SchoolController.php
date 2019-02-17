<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Entity\School;
use App\Form\SchoolType;

class SchoolController extends AbstractController
{
    public function showAll(Request $request)
    {
        $limit = $request->get('limit', 2);
        $offset = $request->get('offset', 0);
        
        $repository = $this->getDoctrine()->getManager()->getRepository(School::class);
        $schools = $repository->findBy([], [], $limit, $offset);
        $totalSchoolsAmount = $repository->countBy([]);

        return $this->json([
            'data' => $schools,
            'count' => $totalSchoolsAmount,
        ]);
    }

    public function showOne($id)
    {
        $school = $this->getDoctrine()->getManager()->getRepository(School::class)->find($id);
        
        if (!$school) {
            return $this->json([
                'message' => 'Szkoła o podanym id nie istnieje',
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json(['data' => $school]);
    }

    public function add(Request $request)
    {
        $school = new School();

        $form = $this->createForm(SchoolType::class, $school);
        $form->submit(json_decode($request->getContent(), true));

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($school);
            $entityManager->flush();

            return $this->json([
                'data' => $school
            ], Response::HTTP_CREATED);
        }

        return $this->json([
            'message' => 'Błąd walidacji'
        ], Response::HTTP_BAD_REQUEST);
    }

    public function update(Request $request, $id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $repository = $entityManager->getRepository(School::class);

        $school = $repository->find($id);
        if (!$school) {
            return $this->json([
                'message' => 'Szkoła o podanym id nie istnieje'
            ], Response::HTTP_NOT_FOUND);
        }
        
        $form = $this->createForm(SchoolType::class, $school);
        $form->submit(json_decode($request->getContent(), true));

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($school);
            $entityManager->flush();

            return $this->json([
                'data' => $school,
                'message' => 'Pomyślnie zaktualizowano',
            ], Response::HTTP_CREATED);
        }

        return $this->json([
            'message' => 'Błąd formularza'
        ], Response::HTTP_BAD_REQUEST);
    }

    public function delete(Request $request, $id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $repository = $entityManager->getRepository(School::class);

        $school = $repository->find($id);
        if (!$school) {
            return $this->json([
                'message' => 'Szkoła o podanym id nie istnieje',
            ], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($school);
        $entityManager->flush();

        return $this->json([
            'message' => 'Szkoła usunięta',
        ]);
    }
}
