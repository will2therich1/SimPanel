<?php
/**
 * The core admin symfony controller.
 *
 * @author William Rich
 * @copyright https://servers4all.documize.com/s/Wm5Pm0A1QQABQ1xw/simpanel/d/WnDQ5EA1QQABQ154/simpanel-license
 */



namespace App\Controller\Admin\Core;

use App\Service\Core\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Service\Core\DataCompiler;

class AdminCoreController extends Controller
{

    /**
     * @var DataCompiler|null
     */
    private $dataCompiler = null;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(DataCompiler $dataCompiler , EntityManagerInterface $em)
    {
        $this->dataCompiler = $dataCompiler;
        $this->em = $em;
    }

    public function index()
    {
        return $this->render('admin_core/index.html.twig' , $this->dataCompiler->createDataArray('Dash'));
    }

    public function userIndexPage(Request $request, PaginationService $paginationService)
    {
        $dataArray = $this->dataCompiler->createDataArray('Users');

        // Build our form
        $form = $this->createSearchForm();
        // Handle the current request
        $form->handleRequest($request);
        // Create the query builder
        $queryBuilder = $this->em->createQueryBuilder();

        // Get our offsets and limits
        $offset = $paginationService->getOffset($request);
        $limit = $paginationService->getLimit($request);

        // Form validation and submission
        if ($form->isSubmitted() && $form->isValid())
        {
            $formData = $form->getData();

            if($formData['name'] !== null)
            {
                // Query Via name.
                $queryBuilder->select('u')
                  ->from('App:User', 'u')
                  ->where('u.username LIKE :name')
                  ->andWhere('u.admin = 0')
                  ->setMaxResults($limit)
                  ->setFirstResult($offset)
                  ->setParameter('name', '%' . $formData['name'] . '%');

            }elseif ($formData['id'] !== null){

                // Query searching via Id
                $queryBuilder->select('u')
                  ->from('App:User', 'u')
                  ->where('u.id = :id')
                  ->andWhere('u.admin = 0')
                  ->setMaxResults($limit)
                  ->setFirstResult($offset)
                  ->setParameter('id', $formData['id']);

            }else{
                $dataArray['error'] = "You need to input data into one of the fields before searching";
                $queryBuilder->select('u')
                  ->from('App:User', 'u')
                  ->where('u.admin = 0')
                  ->setMaxResults($limit)
                  ->setFirstResult($offset);
            }
        }else{
            $queryBuilder->select('u')
              ->from('App:User', 'u')
              ->where('u.admin = 0')
              ->setMaxResults($limit)
              ->setFirstResult($offset);
        }

        // Get the user Result
        $result = $queryBuilder->getQuery()->execute();
        // Create the data array
        $dataArray['form'] = $form->createView();
        $dataArray['users'] = $result;
        return $this->render('admin_core/users/users.index.html.twig' , $dataArray);

    }

    /**
     * Creates a form for searching used on admins,users & network pages.
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createSearchForm()
    {
        // Create the search form
        $form = $this->createFormBuilder()
          ->setMethod('POST')
          ->add('name', TextType::class , array(
            'attr' => array(
              'class' => 'form-control form-control-success',
              'id' => 'inputHorizontalSuccess',
              'placeholder' => 'Name',
            ),
            'label' => 'Name',
            'required' => false,
          ))
          ->add('id', TextType::class , array(
            'attr' => array(
              'class' => 'form-control form-control-success',
              'id' => 'inputHorizontalSuccess',
              'placeholder' => 'ID',
            ),
            'label' => 'ID',
            'required' => false,
          ))
          ->add('search', SubmitType::class , array(
            'attr' => array(
              'class' => 'btn btn-primary',
              'style' => 'margin: 10px;',
            ),
          ))
          ->getForm();

        return $form;
    }

}
