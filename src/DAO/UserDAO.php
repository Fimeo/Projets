<?php


namespace App\src\DAO;

use App\config\Parameter;

/**
 * Class UserDAO, gère les accès base de donnée pour les comptes utilisateurs
 * @package App\src\DAO
 */
class UserDAO extends DAO
{
    /**
     * Enregistre un utilisateur dans la base de données, mot de passe haché
     * @param Parameter $post Données utilisateur
     */
    public function register(Parameter $post)
    {
        $hashpassword = password_hash($post->get('password'), PASSWORD_DEFAULT);
        $sql = 'INSERT INTO user (pseudo, password, createdAt) VALUES (:pseudo, :password, NOW())';
        $this->createQuery($sql, [
            'pseudo' => $post->get('pseudo'),
            'password' => $hashpassword
        ]);
    }

    /**
     * Vérification si il n'existe pas déjà un compte pour cet utilisateur
     * Vérifaction stricte sur le pseudo, insensible à la casse
     * @param Parameter $post Données utilisateur
     */
    public function checkUser(Parameter $post)
    {
        $sql = 'SELECT COUNT(pseudo) FROM user WHERE pseudo=:pseudo';
        $result = $this->createQuery($sql, [
            'pseudo' => $post->get('pseudo')
        ]);
        $isUnique = $result->fetchColumn();
        if ($isUnique) {
            return "<p>Le pseudo existe déjà</p>";
        }
    }

    /**
     * Vérification des données de connexion pour un utilisateur
     * @param Parameter $post Données de connexion
     * @return array
     */
    public function login(Parameter $post)
    {
        //TODO : vérification si bien un seuil utilisateur avec ce pseudo !
        $sql = 'SELECT id, pseudo, password FROM user WHERE pseudo=:pseudo';
        $data = $this->createQuery($sql, [
            'pseudo' => $post->get('pseudo')
        ]);
        $result = $data->fetch();
        if ($result) {
            $isPasswordValid = password_verify($post->get('password'), $result['password']);
            if ($isPasswordValid) {
                return [
                    'result' => $result,
                    'isPasswordValid' => $isPasswordValid
                ];
            }

        }
        return [];
    }

    /**
     * Mise à jour du mot de passe de l'utilisateur userProfile dans la base de données
     * @param Parameter $post Nouveau mot de passe
     * @param $userProfile mixed Profile de l'utilisateur concerné
     * @return false Si une erreur survient
     */
    public function updatePassword(Parameter $post, $userProfile)
    {
        if (key_exists('password', $post)) {
            $sql = 'UPDATE user SET password=:password WHERE id=:id AND pseudo=:pseudo';
            $this->createQuery($sql, [
                //'password' => password_hash($post->get('password'), PASSWORD_DEFAULT),
                'id' => $userProfile['id'],
                'pseudo' => $userProfile['pseudo'],
                'password' => $post->get('password')
            ]);
        }
        return false;
        //TODO : gérer si retourne false, mettre un autre msg
    }
}