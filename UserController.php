<?php

namespace AppBundle\Controller;

use AppBundle\Form\UserFormType;
use AppBundle\Entity\User;
use AppBundle\Entity\UserCollection;
use AppBundle\Entity\UserType;
use AppBundle\Entity\ChecklistItem;
use AppBundle\UserManager;
use AppBundle\Entity\Util;
use AppBundle\Entity\Loan;
use AppBundle\Entity\LoanParameter;
use AppBundle\Entity\LoanValue;
use AppBundle\Entity\GroupParameter;

use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints\DateTime;

use Doctrine\ORM\EntityRepository;
/**
 * Rest controller for users
 *
 * @package AppBundle\Controller
 * @author Gordon Franke <info@nevalon.de>
 */
class UserController extends FOSRestController
{
    private $userType;
    private $userManager;
    private $viewerRole ;

    public function getUserType(){
        return new UserType();
    }

    public function isUserlogedIn(){
        return $this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY');
    }

    /**
    *
    */
    public function getCurrentUser(){

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        $useremail = $this->container->get('session')->get("current_user_email");
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle\Entity\User');
        $user = $repo->findOneByEmail($useremail);
        if (null != $user){
            return $user;
        }else {
            return null;
        }
    }

    public function updateChecklist($checklist)
    {
        $em = $this->getDoctrine()->getManager();
        $oldchecklist = $em->getReference('AppBundle\Entity\ChecklistItem',$checklist->id);
        $oldchecklist = Util::copyObject($oldchecklist,$checklist);
        $oldchecklist->updatedAt = new \DateTime('now');
        $em->persist($oldchecklist);
        $em->flush();
    }

    public function saveNewChecklist($checklist)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle\Entity\ChecklistItem');
        $checklist->createAt = new \DateTime('now');
        $checklist->updatedAt = new \DateTime('now');
        $em->persist($checklist);
        $em->flush();
    }

    public function makeBrokerChecklist($loan_id,$broker_id){
        $checklists = $this->getChecklistsByLoan($loan_id);
        foreach ($checklists as $checklist) {
            $newChecklist = new ChecklistItem();
            $newChecklist->loan_id = $loan_id;
            $newChecklist->name = $checklist->name;
            $newChecklist->description =  $checklist->description;
            $newChecklist->broker_id = $broker_id;
            $this->saveNewChecklist($newChecklist);
        }
    }

    public function saveNewLoanParameter($loanparam){
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle\Entity\LoanParameter');
        $em->persist($loanparam);
        $em->flush();
    }

    public function removeLoanParameterById($id){
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle\Entity\LoanParameter');
        $loanparam = $this->getLoanParameterById($id);
        $em->remove($loanparam);
        $em->flush();
    }

    public function getLoanParameterById($id){
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle\Entity\LoanParameter');
        return $repo->findOneById($id);
    }

    public function getGroupParameterById($id){
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle\Entity\GroupParameter');
        return $repo->findOneById($id);
    }


    public function getAllLoanGroupParams()
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle\Entity\GroupParameter');
        $grpParams = $repo->findAll();
        foreach ($grpParams as $grpParam) {
            $repo = $em->getRepository('AppBundle\Entity\LoanParameter');
            $grpParam->loanParameters = $repo->findBy(array('group_id' => $grpParam->id ));
        }
        $em->flush();
        return $grpParams;
    }


    public function getChecklistsByLoan($loan_id, $broker_id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle\Entity\ChecklistItem');
        $checkListItems = $repo->findBy(array("loan_id" => $loan_id,
                                               "broker_id" => $broker_id));
        $em->flush();
        return $checkListItems;
    }

    public function deleteLoan($loan_id){
        $em = $this->getDoctrine()->getEntityManager();
        $loan = $em->getRepository('AppBundle\Entity\Loan')->findOneById($id);
        $userRole = $this->getUserType()->getRole($currentuser->type);
        $tempLoan = $loan;
        $this->deleteLoanValues($loan);
        $em->remove($loan);
        $em->flush();
        return $tempLoan;
    }


    public function saveNewLoan($loan)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle\Entity\Loan');
        $loan->createAt = new \DateTime('now');
        $loan->updatedAt = new \DateTime('now');
        $em->persist($loan);
        $em->flush();
        $this->saveLoanValues($loan);
    }


    public function deleteLoanValues($loan)
    {
        $em = $this->getDoctrine()->getManager();
        $dql = " DELETE  FROM \AppBundle\Entity\LoanValue l WHERE l.loan_id = {$loan->id} ";
        $em->createQuery($dql)->getResult();
        $em->flush();

    }

    public function saveLoanValues($loan)
    {
        $this->deleteLoanValues($loan);

        $em = $this->getDoctrine()->getManager();
        foreach ($loan->loanValues as $value) {
            $repo = $em->getRepository('AppBundle\Entity\LoanValue');
            $loanValue = new LoanValue();
            $loanValue->loan_id = $loan->id;
            $loanValue->group_id = $value['key'];
            $loanValue->loanparam_id = $value['value'];
            $em->persist($loanValue);
            $em->flush();
        }
    }

    public function updateLoan($loan)
    {
        $em = $this->getDoctrine()->getManager();
        $oldLoan = $em->getReference('AppBundle\Entity\Loan',$loan->id);
        $oldLoan = Util::copyObject($oldLoan,$loan);
        $oldLoan->updatedAt = new \DateTime('now');
        $em->persist($oldLoan);
        $em->flush();
        $this->saveLoanValues($loan);
    }

    public function getLender($lender_id){
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle\Entity\Lender');
        return $repo->findOneById($lender_id);
    }

    public function updateLender($lender){
        $em = $this->getDoctrine()->getManager();
        $oldLender = $em->getReference('AppBundle\Entity\Lender',$lender->id);
        $oldLender = Util::copyObject($oldLender,$lender);
        $oldLender->updatedAt = new \DateTime('now');
        $em->persist($oldLender);
        $em->flush();
    }


    public function getAllLenders(){
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle\Entity\Lender');
        return $repo->findAll();
    }

    public function updateEmployees($lender_id,$employee_ids)
    {   //Clear old employees.
        $oldemployees = $this->getEmployees($lender_id);
        foreach ($oldemployees as $user) {
            $user->employer_id = null;
            $this->updateUser($user);
        }

        //add new employees.
        foreach ($employee_ids as $employee_id) {
            $user = $this->getUserById($employee_id);
            $user->employer_id = $lender_id;
            $this->updateUser($user);
        }
    }


    public function getEmployees($lender_id)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle\Entity\User');
        $employees = $repo->findBy(array("employer_id" => $lender_id));
        $em->flush();
        return $employees;
    }

    public function getAvailableLoanTypes(){
        $em = $this->getDoctrine()->getManager();
    //    $sql =  "SELECT l.type , count(l.id) as loan_count FROM \AppBundle\Entity\Loan l GROUP BY l.type ";

        $sql =  "SELECT l.name as type , count(l.id) as loan_count FROM \AppBundle\Entity\Loan l GROUP BY l.name ";

        return $em->createQuery($sql)->getScalarResult();
    }
    public function getAllLoans($params)
    {   $where = '';
        foreach ($params as $value) {
            $where .= " AND ".$value['key']." = '".$value['value']."' ";
        }
        $em = $this->getDoctrine()->getManager();
        $dql = " SELECT l.id,l.lender_id, l.name, v.group_id, v.loanparam_id,g.name AS Key ,lp.Value ".
            " FROM \AppBundle\Entity\Loan l ".
            " INNER JOIN \AppBundle\Entity\LoanValue v WITH l.id = v.loan_id ".
            " INNER JOIN \AppBundle\Entity\LoanParameter lp WITH v.loanparam_id = lp.id ".
            " INNER JOIN \AppBundle\Entity\GroupParameter g WITH v.group_id = g.id ".
            " WHERE 1 = 1 {$where} ".
            " ORDER BY l.id, g.name , lp.Value ";
        $temploans = $em->createQuery($dql)->getResult();
        $loans = [];
        $loanParams = [];
        for($i = 0; $i < count($temploans) - 1 ; $i++ )
        {   if($i+1 == count($temploans)-1)
            {
                $loanparam =  new LoanParameter();
                $loanparam->group_id = $temploans[$i]['group_id'];
                $loanparam->loanparam_id = $temploans[$i]['loanparam_id'];
                $loanparam->key = $temploans[$i]['Key'];
                $loanparam->value = $temploans[$i]['Value'];
                $loanParams[] = $loanparam ;
                $loan = new Loan();
                $loan->id = $temploans[$i]['id'];
                $loan->lender_id = $temploans[$i]['lender_id'];
                $loan->name = $temploans[$i]['name'];
                $loan->loanValues = $loanParams;
                $loans[] = $loan;
                break;
            }
            if( $temploans[$i]['id'] == $temploans[$i+1]['id'])
            {
                $loanparam =  new LoanParameter();
                $loanparam->group_id = $temploans[$i]['group_id'];
                $loanparam->loanparam_id = $temploans[$i]['loanparam_id'];
                $loanparam->key = $temploans[$i]['Key'];
                $loanparam->value = $temploans[$i]['Value'];
                $loanParams[] = $loanparam ;

            }else{
                $loan = new Loan();
                $loan->id = $temploans[$i]['id'];
                $loan->lender_id = $temploans[$i]['lender_id'];
                $loan->name = $temploans[$i]['name'];
                $loan->loanValues = $loanParams;
                $loans[] = $loan;

                $loanParams = [];
            }
        }
        return $loans;
    }

    public function getLoan($id)
    {
        $loans = $this->getAllLoans(array(['key'=>'l.id','value'=>$id]));
        return $loans[0];
    }


    public function getLoansByLender($lender_id)
    {
        $loans = $this->getAllLoans(array(['key'=>'l.lender_id','value'=>$lender_id]));
        return $loans;
    }

    public function getLenderById($lender_id){
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle\Entity\Lender');
        $lender = $repo->findOneById($lender_id);
        $em->flush();
        return $lender;
    }

    public function getBrokerDashboardUsers($broker_Id){
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle\Entity\User');
        $em->flush();
        return $repo->findBy(array("parent_id" => $broker_Id,
                                    'type' => UserType::BORROWER));
    }

    public function getAdminDashboardUsers()
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle\Entity\User');
        $em->flush();
        return $repo->findByType(array(UserType::ADMIN,
                                    UserType::BROKER,
                                    UserType::BORROWER));
    }

    public function getUsersByType($type_id)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle\Entity\User');
        $em->flush();
        return $repo->findByType($type_id);
    }

    public function getUserById($id){
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle\Entity\User');
        $em->flush();
        return $repo->findOneById($id);
    }

    public function updateUser($user){
        $em = $this->getDoctrine()->getManager();
        $oldUser = $em->getReference('AppBundle\Entity\User',$user->id);
        $oldUser = Util::copyObject($oldUser,$user);
        $oldUser->updatedAt = new \DateTime('now');
        $em->persist($oldUser);
        $em->flush();
    }




        /**
         * Get a single user.
         *
         * @ApiDoc(
         *   output = "AppBundle\Entity\User",
         *   statusCodes = {
         *     200 = "Returned when successful",
         *     404 = "Returned when the user is not found"
         *   }
         * )
         *
         * @Annotations\View(templateVar="user")
         *
         * @param int $id the user id
         *
         * @return array
         *
         * @throws NotFoundHttpException when user not exist
         */
        public function homeAction()
        {
            if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
                throw $this->createAccessDeniedException();
            }
           // the above is a shortcut for this
           $user = $this->get('security.token_storage')->getToken()->getUser();
           $this->container->get('session')->set("current_user_email",$user->getEmail());
           $userTypeName = $this->getUserType()->getName($user->type);
           if (null == $user) {
                throw $this->createNotFoundException("User does not exist.");
            }
            //return $this->render('@AppBundle/Admin/dashboard.html.twig',array('user'=>$user));
            return $this->redirectToRoute('_'.$userTypeName.'_dashboard');
        }

        public function user403Action()
        {
            return $this->render('@AppBundle/User/error403.html.twig');
        }

    /**
     * List all users.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Annotations\QueryParam(name="offset", requirements="\d+", nullable=true, description="Offset from which to start listing users.")
     * @Annotations\QueryParam(name="limit", requirements="\d+", default="10", description="How many users to return.")
     * @Annotations\View()
     *
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @return array
     */
    public function getUsersAction(ParamFetcherInterface $paramFetcher,$role,$parent_id = null )
    {
        $offset = $paramFetcher->get('offset');
        $start = null == $offset ? 0 : $offset + 1;
        $limit = $paramFetcher->get('limit');

        $userType = $this->getUserType()->getUserTypeObj($role);
        $currentuser = $this->getCurrentUser();
        if($userType["id"] > $currentuser->type || $currentuser->type == 1)
            $where =  array('type' => $userType['id']);
        else {
            throw $this->createAccessDeniedException();
        }

        if($parent_id){
            $where['parent_id'] = $parent_id;
        }

        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle\Entity\User');
        $users = $repo->findBy($where);
        $em->flush();

        return $this->render('@AppBundle/User/getUsers.html.twig',
                                array('users'=> $users,
                                     "offset" => $offset,
                                     "limit" => $limit,
                                     "userType" => $userType ,
                                     "current_user" => $currentuser));
    }


    /**
     * Presents the form to use to create a new user.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     * @Annotations\QueryParam(name="usertype", description="User type.")
     * @Annotations\View()
     *
     * @return FormTypeInterface
     */
    public function newUserAction($usertypeid = 0)
    {
        $userTypeName = $this->getUserType()->getName($usertypeid);
        $userTypeValue = $this->getUserType()->getValue($usertypeid);

        $userFormtype = new UserFormType();
        $userFormtype->setUserType($usertypeid);

        return $this->render('@AppBundle/User/newUser.html.twig',
                array('form' => $this->createForm($userFormtype)->createView(),
                "userTypeName" => $userTypeName,
                'userTypeId'=> $usertypeid,
                'userTypeValue'=> $userTypeValue  ));
    }

    /**
     * Get a single user.
     *
     * @ApiDoc(
     *   output = "AppBundle\Entity\User",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   }
     * )
     *
     * @Annotations\View(templateVar="user")
     *
     * @param int $id the user id
     *
     * @return array
     *
     * @throws NotFoundHttpException when user not exist
     */
    public function getUserAction($id)
    {
        echo "UserController:getUserAction userId ".$id;
        $user = $this->getUserById($id);
        $userTypeName = $this->getUserType()->getName($user->type);
        if (null == $user) {
             throw $this->createNotFoundException("User does not exist.");
         }
         return $this->redirectToRoute('_'.$userTypeName.'_dashboard',array('id'=>$id));
    }

    /**
     * Creates a new user from the submitted data.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "AppBundle\Form\UserType",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(
     *   template = "AppBundle:User:newUser.html.twig",
     *   statusCode = Response::HTTP_BAD_REQUEST
     * )
     *
     * @param Request $request the request object
     *
     * @return FormTypeInterface[]|View
     */
    public function postUsersAction(Request $request)
    {

        $form = $this->createForm(new UserFormType());
        $form->handleRequest($request);

    //    if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            //var_dump($user);
            //exit;
            if (!isset($user->id) || $user->id == 0){
                $user->setCreateAt(new \DateTime('now'));
                $user->token = base64_encode(openssl_random_pseudo_bytes(64));

                $user->setUpdatedAt(new \DateTime('now'));
                $em = $this->getDoctrine()->getManager();
                $repo = $em->getRepository('AppBundle\Entity\User');
                $em->persist($user);
                $userTypeName = $this->getUserType()->getName($user->getType());
                $em->flush();
            }

    //        return $this->redirectToRoute('_'.$userTypeName.'_list');
    //    }

        return $this->render('@AppBundle/User/newUser.html.twig', array(
                                    'form' => $form->createView()
                                ));
    }


    private function encodePassword(User $user, $plainPassword)
    {
        $encoder = $this->container->get('security.encoder_factory')
            ->getEncoder($user);

        return $encoder->encodePassword($plainPassword, $user->getSalt());
    }

    /**
     * Presents the form to use to update an existing user.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes={
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   }
     * )
     *
     * @Annotations\View()
     *
     * @param int $id the user id
     *
     * @return FormTypeInterface
     *
     * @throws NotFoundHttpException when user not exist
     */
    public function editUsersAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle\Entity\User');
        $user = $repo->find($id);
        if (null == $user) {
            return $this->render('@AppBundle/User/newUsers.html.twig',
            array('form' => $this->createForm(new UserFormType())->createView(),
            "user"=>$user,
            "error" => "User does not exist."));
        }
        $currentuser = $this->getCurrentUser();
        return $this->render('@AppBundle/User/editUser.html.twig',
                array('form' => $this->createForm(new UserFormType(), $user)->createView(),
                "user"=>$user,
                 "current_user" => $currentuser));
    }

    /**
     * Update existing user from the submitted data or create a new user at a specific location.
     *
     * @ApiDoc(
     *   resource = true,
     *   input = "AppBundle\Form\UserType",
     *   statusCodes = {
     *     201 = "Returned when a new resource is created",
     *     204 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @Annotations\View(
     *   template="AppBundle:User:editUser.html.twig",
     *   templateVar="form"
     * )
     *
     * @param Request $request the request object
     * @param int     $id      the user id
     *
     * @return FormTypeInterface|RouteRedirectView
     *
     * @throws NotFoundHttpException when user not exist
     */
    public function putUsersAction(Request $request, $id)
    {
            $currentuser = $this->getCurrentUser();
            $userTypeName = $this->getUserType()->getName($currentuser->type);
            $userRole = $this->getUserType()->getRole($currentuser->type);
            $errors = '' ;
            $em = $this->getDoctrine()->getEntityManager();
            $user = $em->getRepository('AppBundle\Entity\User')->find($id);

            $form = $this->createForm(new UserFormType(),$user);
            if ('PUT' == $request->getMethod()) {
                 $form->bind($request);
                 if ($form->isValid()) {
                    $user = $form->getData();
                    $this->updateUser($user);

                    if($currentuser->type == 1)
                        return $this->redirectToRoute('_admins_dashboard', array('role' => $userRole ));
                    else if ($currentuser->type == 2 || ($currentuser->type == 3)){
                        return $this->redirectToRoute('get_lender', array('id' => $currentuser->employer_id ));
                    }else{
                        return $this->redirectToRoute('_'.$userTypeName.'_list', array('role' => $userRole ));
                    }

                }else {
                    foreach ($form->getErrors() as $key => $error) {
                        $errors .= $error->getMessage();
                        echo 'Errors => '.$errors ;
                    }
                }

            }

            return $this->render('@AppBundle/User/editUser.html.twig',
                    array('form' => $this->createForm(new UserFormType(), $user)->createView(),
                    "user"=>$user,
                     "current_user" => $currentuser,
                    "errors"=> $errors));

        }

    /**
     * Removes a user.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes={
     *     204="Returned when successful"
     *   }
     * )
     *
     * @param int $id the user id
     *
     * @return View
     */
    public function deleteUsersAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $em->getRepository('AppBundle\Entity\User')
                ->findOneById($id);
        $userTypeName = $this->getUserType()->getName($user->type);
        $userRole = $this->getUserType()->getRole($user->type);
        $tempUser = $user;
        $em->remove($user);
        $em->flush();
        if($tempUser->employer_id > 0){
            return $this->redirectToRoute('get_lender', array('id' => $tempUser->employer_id));
        }
        return $this->redirectToRoute('_'.$userTypeName.'_list', array('role' => $userRole));
    }

    /**
     * Removes a user.
     *
     * @ApiDoc(
     *   resource = true,
     *   statusCodes={
     *     204="Returned when successful"
     *   }
     * )
     *
     * @param int $id the user id
     *
     * @return View
     */
    public function removeUsersAction($id)
    {
        return $this->deleteUsersAction($id);
    }
}
