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

    public function __construct(DataCompiler $dataCompiler, EntityManagerInterface $em)
    {
        $this->dataCompiler = $dataCompiler;
        $this->em = $em;
    }

    public function index()
    {
        $dataArray = $this->dataCompiler->createDataArray('Dash');

        $this->getUser();

        try {
            $dataArray['block'] = [
                'users' => $this->getUserCount(),
                'admins' => $this->getAdminCount(),
                'servers' => $this->getNetworkServerCount(),
            ];
        } catch (\Exception $e) {
            error_log('Failed to get counts for users,admins & network servers error as follows: '.$e->getMessage());
            $dataArray['block'] = ['users' => 'unknown', 'admins' => 'unknown', 'servers' => 'unknown'];
        }

        return $this->render('admin_core/index.html.twig', $dataArray);
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
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            if (null !== $formData['name']) {
                // Query Via name.
                $queryBuilder->select('u')
                    ->from('App:User', 'u')
                    ->where('u.username LIKE :name')
                    ->andWhere('u.admin = 0')
                    ->setMaxResults($limit)
                    ->setFirstResult($offset)
                    ->setParameter('name', '%'.$formData['name'].'%');
            } elseif (null !== $formData['id']) {
                // Query searching via Id
                $queryBuilder->select('u')
                    ->from('App:User', 'u')
                    ->where('u.id = :id')
                    ->andWhere('u.admin = 0')
                    ->setMaxResults($limit)
                    ->setFirstResult($offset)
                    ->setParameter('id', $formData['id']);
            } else {
                $dataArray['error'] = 'You need to input data into one of the fields before searching';
                $queryBuilder->select('u')
                    ->from('App:User', 'u')
                    ->where('u.admin = 0')
                    ->setMaxResults($limit)
                    ->setFirstResult($offset);
            }
        } else {
            $queryBuilder->select('u')
                ->from('App:User', 'u')
                ->where('u.admin = 0')
                ->setMaxResults($limit)
                ->setFirstResult($offset);
        }

        // Get pagination links
        $dataArray['pages'] = $paginationService->createPagination('/admin/user', $offset, $limit);

        // Get the user Result
        $result = $queryBuilder->getQuery()->execute();
        // Create the data array
        $dataArray['form'] = $form->createView();
        $dataArray['users'] = $result;

        return $this->render('admin_core/users/users.index.html.twig', $dataArray);
    }

    public function adminIndexPage(Request $request, PaginationService $paginationService)
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
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            if (null !== $formData['name']) {
                // Query Via name.
                $queryBuilder->select('u')
                    ->from('App:User', 'u')
                    ->where('u.username LIKE :name')
                    ->andWhere('u.admin = 1')
                    ->setMaxResults($limit)
                    ->setFirstResult($offset)
                    ->setParameter('name', '%'.$formData['name'].'%');
            } elseif (null !== $formData['id']) {
                // Query searching via Id
                $queryBuilder->select('u')
                    ->from('App:User', 'u')
                    ->where('u.id = :id')
                    ->andWhere('u.admin = 1')
                    ->setMaxResults($limit)
                    ->setFirstResult($offset)
                    ->setParameter('id', $formData['id']);
            } else {
                $dataArray['error'] = 'You need to input data into one of the fields before searching';
                $queryBuilder->select('u')
                    ->from('App:User', 'u')
                    ->where('u.admin = 1')
                    ->setMaxResults($limit)
                    ->setFirstResult($offset);
            }
        } else {
            $queryBuilder->select('u')
                ->from('App:User', 'u')
                ->where('u.admin = 1')
                ->setMaxResults($limit)
                ->setFirstResult($offset);
        }

        // Get pagination links
        $dataArray['pages'] = $paginationService->createPagination('/admin/admins', $offset, $limit);

        // Get the user Result
        $result = $queryBuilder->getQuery()->execute();
        // Create the data array
        $dataArray['form'] = $form->createView();
        $dataArray['users'] = $result;

        return $this->render('admin_core/admins/admins.index.html.twig', $dataArray);
    }

    public function networkIndexPage(Request $request, PaginationService $paginationService)
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
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            if (null !== $formData['name']) {
                // Query Via name.
                $queryBuilder->select('u')
                    ->from('App:NetworkServer', 'u')
                    ->where('u.server_name LIKE :name')
                    ->setMaxResults($limit)
                    ->setFirstResult($offset)
                    ->setParameter('name', '%'.$formData['name'].'%');
            } elseif (null !== $formData['id']) {
                // Query searching via Id
                $queryBuilder->select('u')
                    ->from('App:NetworkServer', 'u')
                    ->where('u.id = :id')
                    ->setMaxResults($limit)
                    ->setFirstResult($offset)
                    ->setParameter('id', $formData['id']);
            } else {
                $dataArray['error'] = 'You need to input data into one of the fields before searching';
                $queryBuilder->select('u')
                    ->from('App:NetworkServer', 'u')
                    ->setMaxResults($limit)
                    ->setFirstResult($offset);
            }
        } else {
            $queryBuilder->select('u')
                ->from('App:NetworkServer', 'u')
                ->setMaxResults($limit)
                ->setFirstResult($offset);
        }

        // Get pagination links
        $dataArray['pages'] = $paginationService->createPagination('/admin/network', $offset, $limit);

        // Get the user Result
        $result = $queryBuilder->getQuery()->execute();
        // Create the data array
        $dataArray['form'] = $form->createView();
        $dataArray['servers'] = $result;

        return $this->render('admin_core/network/network.index.html.twig', $dataArray);
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
          ->add('name', TextType::class, [
            'attr' => [
              'class' => 'form-control form-control-success',
              'id' => 'inputHorizontalSuccess',
              'placeholder' => 'Name',
            ],
            'label' => 'Name',
            'required' => false,
          ])
          ->add('id', TextType::class, [
            'attr' => [
              'class' => 'form-control form-control-success',
              'id' => 'inputHorizontalSuccess',
              'placeholder' => 'ID',
            ],
            'label' => 'ID',
            'required' => false,
          ])
          ->add('search', SubmitType::class, [
            'attr' => [
              'class' => 'btn btn-primary',
              'style' => 'margin: 10px;',
            ],
          ])
          ->getForm();

        return $form;
    }

    /**
     * Counts the amount of users in the database.
     *
     * @return string - Returns a string with the amount of admins in the database
     *
     * @throws \Exception - If something goes wrong!
     */
    public function getUserCount()
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $userCountQuery = $queryBuilder->select('count(u)')
            ->from('App:User', 'u')
            ->Where('u.admin = 0');
        $userCountQueryCount = $userCountQuery->getQuery()->getSingleScalarResult();

        return $userCountQueryCount;
    }

    /**
     * Counts the amount of admins in the database.
     *
     * @return string - Returns a string with the amount of admins in the database
     *
     * @throws \Exception - If something goes wrong!
     */
    public function getAdminCount()
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $userCountQuery = $queryBuilder->select('count(u)')
            ->from('App:User', 'u')
            ->Where('u.admin = 1');
        $userCountQueryCount = $userCountQuery->getQuery()->getSingleScalarResult();

        return $userCountQueryCount;
    }

    /**
     * Counts the amount of network servers in the database.
     *
     * @return string - Returns a string with the amount of admins in the database
     *
     * @throws \Exception - If something goes wrong!
     */
    public function getNetworkServerCount()
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $serverCountQuery = $queryBuilder->select('count(u)')
            ->from('App:NetworkServer', 'u');
        $serverCountQueryCount = $serverCountQuery->getQuery()->getSingleScalarResult();

        return $serverCountQueryCount;
    }
}
