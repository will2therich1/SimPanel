<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;


class AdminController extends Controller
{
    /**
     * @Route("/admin", name="adminHomepage")
     */
    public function adminIndexAction(Request $request)
    {

        $user = $this->getUser();


        // Generate the user information
        $firstName = $user->getFirstName();
        $lastName = $user->getLastName();
        $fullName = $firstName . " " . $lastName;


        // Prepare data for page
        $data = [];
        $data['name'] = $fullName;


        // replace this example code with whatever you need
        return $this->render('admin/admin.index.html.twig' , $data);
    }

}
