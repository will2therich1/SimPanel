<?php
/**
 * Simpanels user management service.
 *
 * @author William Rich
 * @copyright https://servers4all.documize.com/s/Wm5Pm0A1QQABQ1xw/simpanel/d/WnDQ5EA1QQABQ154/simpanel-license
 */

namespace App\Service\User;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserManagementService
{
    /**
     * @var EntityManagerInterface - Doctrines entity manager
     */
    private $em;

    /**
     * @var user - Currently logged in user
     */
    private $user;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(EntityManagerInterface $em, TokenStorageInterface $tokenStorage)
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
    }

    public function storeUser()
    {
        $this->user = $this->tokenStorage->getToken()->getUser();
    }

    /**
     * Creates us a new user, it verifies the username & email are unique and that password confirmations are there!.
     *
     * @param array $userData - The user data array as defined below
     *
     * array['first name'] - the users first name
     * array['last name'] - the users last name
     * array['username'] - the users username
     * array['email'] - the users email
     * array['password'] - the users password = NOT MANDATORY if not provided then a random one will be generated.
     * array['password_confirm'] - If setting a password then this should be the same as Password to confirm it is right.
     * array['admin] - 1 or 0 to say if the user is a admin
     * array['subuser] - 1 or 0 to say if the user is a sub user
     * array['subuser permissions'] - if subuser what are the permissions
     * array['subuser for'] - the user that the new user is a sub user for
     *
     * @throws \Exception - If anything goes wrong!
     *
     * @return User - The new user object
     */
    public function createUser($userData)
    {
        $newUser = new User();

        // Set these incase they are needed later
        $error = false;
        $errorMessage = '';

        $newUser->setFirstName($userData['first name']);
        $newUser->setLastName($userData['last name']);

        // Check the username is unique
        if ($this->checkIfEmailAndUsernameAreUnique($userData['username'])) {
            $newUser->setUsername($userData['username']);
        } else {
            $error = true;
            $errorMessage .= 'ERROR: Username is not unique, ';
        }

        if ($this->checkIfEmailAndUsernameAreUnique($userData['email'])) {
            $newUser->setEmail($userData['email']);
        } else {
            $error = true;
            $errorMessage .= 'ERROR: Email is not unique, ';
        }

        $newUser->setStatus(1);
        $newUser->setAdmin($userData['admin']);

        if ('' !== $userData['password']) {
            if ($userData['password'] === $userData['password_confirm']) {
                $newUser->setPassword(password_hash($userData['password'], PASSWORD_DEFAULT));
            } else {
                $error = true;
                $errorMessage .= 'ERROR: Passwords didnt match';
            }
        } else {
            $newUser->setPassword(password_hash($this->generatePassword(), PASSWORD_DEFAULT));
        }

        if (1 == $userData['subuser']) {
            $newUser->setSubUser($userData['subuser']);
            $newUser->setSubUserPermissions($userData['subuser permissions']);
            $newUser->setSubUserFor($userData['subuser for']);
        } else {
            $newUser->setSubUser(0);
        }

        $newUser->setTfaStatus(0);
        $newUser->setWhmcsStatus(0);

        if (!$error) {
            return $newUser;
        } else {
            throw new \Exception($errorMessage);
        }
    }

    /**
     * Generates a random password.
     *
     * @param int    $length         Length of the password
     * @param bool   $add_dashes     Add dashes to the password
     * @param string $available_sets Rules to use
     *
     * @return bool|string
     */
    public function generatePassword($length = 9, $add_dashes = false, $available_sets = 'luds')
    {
        $sets = [];
        if (false !== strpos($available_sets, 'l')) {
            $sets[] = 'abcdefghjkmnpqrstuvwxyz';
        }
        if (false !== strpos($available_sets, 'u')) {
            $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        }
        if (false !== strpos($available_sets, 'd')) {
            $sets[] = '23456789';
        }
        if (false !== strpos($available_sets, 's')) {
            $sets[] = '!@#$%&*?';
        }
        $all = '';
        $password = '';
        foreach ($sets as $set) {
            $password .= $set[array_rand(str_split($set))];
            $all .= $set;
        }
        $all = str_split($all);
        for ($i = 0; $i < $length - count($sets); ++$i) {
            $password .= $all[array_rand($all)];
        }
        $password = str_shuffle($password);
        if (!$add_dashes) {
            return $password;
        }
        $dash_len = floor(sqrt($length));
        $dash_str = '';
        while (strlen($password) > $dash_len) {
            $dash_str .= substr($password, 0, $dash_len).'-';
            $password = substr($password, $dash_len);
        }
        $dash_str .= $password;

        return $dash_str;
    }

    /**
     * Checks if a username or email is unique.
     *
     * @param string $username      - The username OR email
     * @param int    $currentUserId - The ID of the user currently being eddited, if a new user then this is not needed/
     *
     * @return bool - true if unique, false if not unique
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function checkIfEmailAndUsernameAreUnique(string $username, int $currentUserId = null)
    {
        $query = $this->em->getRepository('App:User')->createQueryBuilder('u')
            ->where('u.username = :username OR u.email = :email')
            ->setParameter('username', $username)
            ->setParameter('email', $username)
            ->getQuery()
            ->getOneOrNullResult();

        // Run through everything and check if all is valid.
        if (null == $query) {
            return true;
        } else {
            if ($currentUserId == $query->getId()) {
                return true;
            }

            return false;
        }
    }

    /**
     * Updates a user password!.
     *
     * @param array $userData - The user data array as defined below
     * @param User  $user     - The user to update
     *
     * array['current_password'] - the users current password
     * array['new_password'] - the users new password
     * array['new_password_confirm'] - the users new password confirm
     *
     * @throws \Exception - If anything goes wrong!
     *
     * @return user - Updated user object
     */
    public function updateUserPassword($userData, $user)
    {
        $verifyCurrentPassword = password_verify($userData['current_password'], $user->getPassword());

        if ($verifyCurrentPassword) {
            if ($userData['new_password'] === $userData['new_password_confirm']) {
                $user->setPassword(password_hash($userData['new_password'], PASSWORD_DEFAULT));

                return $user;
            } else {
                throw new \Exception('New passwords provided do not match');
            }
        } else {
            throw new \Exception('Unable to verify the current password');
        }
    }
}
