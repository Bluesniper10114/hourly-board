<?php
namespace DAL;

use PDO;
use DAL\Entities\User;

/**
 * Users DAL class
 **/
class UsersDAL extends \Core\Data
{

    /**
     * Login a user
     *
     * @param string $username
     * @param string $password
     *  @return array
     **/
    public function login($username, $password)
    {
        $sql = "SET NOCOUNT ON ;
            DECLARE @return_value int,
            @token nvarchar(max),
            @profileId int,
            @role int,
            @barcode nvarchar(50) = :username,
            @password nvarchar(50) = :password

            EXEC    @return_value = [users].[Login]
            @barcode = @barcode,
            @password = @password,
            @token = @token OUTPUT,
            @profileId = @profileId OUTPUT

            SELECT @role = LevelID from users.Profile where ID = @profileId

            SELECT @token as token,
            @profileId as profileId,
            @role as role,
            @return_value as result
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":username", $username, \PDO::PARAM_STR);
        $stmt->bindValue(":password", $password, \PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $result[0];
    }

    /**
     * Logout current user
     *
     * @param string $token
     * @return boolean
     **/
    public function logout($token)
    {
        try {
            $sql = "
            declare @result int;

            exec @result = [users].[Logout] @token = :token";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(":token", $token, \PDO::PARAM_STR);
            $stmt->execute();
            return true;
        } catch (\PDOException $ex) {
            return false;
        }
    }

    /**
     *  Checks if the token belongs to the profileId
     *
     *  @param string $token
     *  @param int $userProfileId
     *
     *  @return boolean
     **/
    public function doesTokenBelongToProfile($token, $userProfileId)
    {
        try {
            $sql = "
            SET NOCOUNT ON;

            select count(1) as Count
            from users.AccountToken uat
            inner join users.Account ua on ua.id = uat.AccountId
            where Token = :token and ua.ProfileId = :userProfileId and uat.IsActive = 1 and uat.Expire > global.GetDate()";
    
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(":token", $token, \PDO::PARAM_STR);
            $stmt->bindValue(":userProfileId", $userProfileId, \PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result[0]['Count'] === '1';
        } catch (\PDOException $ex) {
            return false;
        }
    }

    /**
     *  Is user token a valid token?
     *
     *  @param string $token
     *
     *  @return boolean
     **/
    public function isTokenValid($token)
    {
        try {
            $sql = "
            SET NOCOUNT ON;

            select count(1) as Count
            from users.AccountToken uat
            inner join users.Account ua on ua.id = uat.AccountId
            where Token = :token and uat.IsActive = 1 and uat.Expire > global.GetDate()";
    
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(":token", $token, \PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result[0]['Count'] === '1';
        } catch (\PDOException $ex) {
            return false;
        }
    }

    /**
     *  Check if token belongs to the username
     *
     *  @param string $token
     *  @param string $username
     *
     *  @return boolean
     **/
    public function tokenBelongsToUsername($token, $username)
    {
        try {
            $sql = "
            SET NOCOUNT ON ;

            select count(1) as Count
            from users.AccountToken uat
            inner join users.Account ua on ua.id = uat.AccountId
            where Token = :token and ua.username = :username and uat.IsActive = 1 and uat.Expire > global.GetDate()
            ";    
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(":token", $token, \PDO::PARAM_STR);
            $stmt->bindValue(":username", $username, \PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result[0]['Count'] === '1';
        } catch (\PDOException $ex) {
            return false;
        }
    }

    /**
     * Get user profile from token string
     *
     * @param string $token
     * @return array
     **/
    public function getProfileFromToken($token)
    {
        $sql = "SET NOCOUNT ON;
        EXEC [users].[ClearExpiredTokens];
        DECLARE @userProfileId BIGINT;

        EXEC @userProfileId = [users].[GetProfileIdFromToken] @Token=:token;
        
        SELECT top 1 id ProfileId, FirstName, LastName, LevelID as Role
        FROM users.Profile
        WHERE id = @userProfileId";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":token", $token, \PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $userData = isset($result[0]) ? $result[0] : [];
        return $userData;
    }

    /**
     *  Set user language
     *
     *  @param string $languageCode
     *  @param int $userId
     *
     *  @return boolean
     **/
    public function setLanguage($languageCode, $userId)
    {
        $sql = "UPDATE [dbo].[UserProfile]
        SET LanguageId = :languageCode
        WHERE id = :userId";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":userId", $userId, \PDO::PARAM_INT);
        $stmt->bindValue(":languageCode", $languageCode, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->rowCount() === 1;
    }

    /**
     * Compare a password with existing password
     *
     * @param int $userProfileId
     * @param string $oldPassword
     *
     * @return boolean
     **/
    public function compareOldPassword($userProfileId, $oldPassword)
    {
        $sql = "DECLARE @oldPassword NVARCHAR(50)

        SELECT @oldPassword = Password
        FROM [dbo].[UserAccount]
        WHERE UserProfileId = :userProfileId

        IF @oldPassword = :oldPassword
        SELECT 1 as Result
        ELSE
        SELECT -1 as Result";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":userProfileId", $userProfileId, \PDO::PARAM_INT);
        $stmt->bindValue(":oldPassword", $oldPassword, \PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return isset($user[0]['Result']) ? intval($user[0]['Result']) === 1 : false;
    }

    /**
     * Change password
     *
     * @param int $userProfileId
     * @param string $newPassword
     *
     * @return bool True if changed
     **/
    public function changePassword($userProfileId, $newPassword)
    {
        $sql = "update users.Account set password = :newPassword where ProfileId = :userProfileId";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":userProfileId", $userProfileId, \PDO::PARAM_INT);
        $stmt->bindValue(":newPassword", $newPassword, \PDO::PARAM_STR);
        $profile = $stmt->execute();
        return true;
    }

    /**
     * Gets a profile from the profile id
     *
     * @param int $userProfileId
     * @return User|null
     **/
    public function getProfileByProfileId($userProfileId)
    {
        $sql = "SELECT TOP 1 p.firstName, p.lastName, p.barcode userName, l.Name levelName, l.Help levelHelp
        FROM users.Profile p
            INNER JOIN users.Level l ON p.LevelId = l.id
        WHERE p.id = :userProfileId";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":userProfileId", $userProfileId, \PDO::PARAM_INT);
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'DAL\Entities\User');
        $stmt->execute();
        $profile = $stmt->fetch();
        return $profile;
    }

    /**
     * Gets user rights by id
     *
     * @param int $userProfileId
     *
     * @return string|null
     **/
    public function getUserRights($userProfileId)
    {
        /* // use this temporary xml to check graphics
        return '<root>
            <timestamp>2018-08-14 23:39:10.550</timestamp>
            <timeout>60</timeout>
            <rights>
                <right LevelID="1">
                <Level>Disabled on</Level>
                <hourly-sign-off enabled="0">1</hourly-sign-off>
                <shift-sign-off enabled="0">1</shift-sign-off>
                </right>
                <right LevelID="2">
                <Level>Disabled off</Level>
                <hourly-sign-off enabled="0">0</hourly-sign-off>
                <shift-sign-off enabled="0">0</shift-sign-off>
                </right>
                <right LevelID="3">
                <Level>Enabled On</Level>
                <hourly-sign-off enabled="1">1</hourly-sign-off>
                <shift-sign-off enabled="1">1</shift-sign-off>
                </right>
                <right LevelID="4">
                <Level>Enabled off</Level>
                <hourly-sign-off enabled="1">0</hourly-sign-off>
                <shift-sign-off enabled="1">0</shift-sign-off>
                </right>
            </rights>
            </root>';
*/
        $sql = "
            declare @xml xml;
            exec users.GetRights @userID = :userProfileId, @xml = @xml OUTPUT
            select @xml as xmlResult
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":userProfileId", $userProfileId, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $xml = isset($result[0]["xmlResult"]) ? $result[0]["xmlResult"] : null;
        return $xml;
    }

    /**
     * Save user rights
     *
     * @param int $userProfileId
     * @param string $xml
     *
     * @return bool True on success
     */
    public function saveUserRights($userProfileId, $xml)
    {
        try {
            $sql = "exec [users].[SaveRights] @userId = :userProfileId, @xml = :xml";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(":userProfileId", $userProfileId, \PDO::PARAM_INT);
            $stmt->bindValue(":xml", $xml, \PDO::PARAM_STR);
            $stmt->execute();
        } catch (\PDOException $ex) {
            return false;
        }
        return true;
    }

    /**
     * Gets all the users
     *
     * @param int $offset Number of users to skip
     * @param int $limit Number of users to load
     * @param string $filter Filters the list
     * @return array of Entities\User
     */
    public function getUsersList($offset, $limit, $filter)
    {
        $sql = "
        DECLARE @filter nvarchar(MAX) = :filter;
        SELECT u.id as userId, l.name as level, p.firstName, p.lastName, u.userName, u.profileId
        FROM users.Account u
            INNER JOIN users.profile p on u.profileId = p.id
            INNER JOIN users.level l on l.id = p.levelId
        WHERE 1=1 and ( p.firstName like @filter or p.lastName like @filter)
        ORDER BY u.id DESC
        OFFSET :offset ROWS FETCH NEXT :limit ROWS ONLY
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":filter", "%$filter%", \PDO::PARAM_STR);
        $stmt->bindValue(":offset", $offset, \PDO::PARAM_INT);
        $stmt->bindValue(":limit", $limit, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_CLASS, 'DAL\Entities\User');
        return $result;
    }

    /**
     * Gets all levels
     *
     * @return array
     */
    public function getLevelsList()
    {
        $sql = "SELECT l.id, l.name FROM users.level l";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * Get user by user profile id
     *
     * @param int $userProfileId
     * @return User|null
     */
    public function getUser($userProfileId)
    {
        $sql = "SELECT u.id as userId, l.id as levelId, l.name as level, p.firstName, p.lastName, u.userName, u.profileId
        FROM users.Account u
            INNER JOIN users.profile p on u.profileId = p.id
            INNER JOIN users.level l on l.id = p.levelId
        WHERE u.id = :userProfileId";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":userProfileId", $userProfileId, \PDO::PARAM_INT);
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'DAL\Entities\User');
        $stmt->execute();
        $result = $stmt->fetch();
        return $result;
    }

    /**
     * Update user data
     *
     * @param User $user
     * @return bool True on success
     */
    public function updateUserData($user)
    {
        $sql = "";
        try {
            $sql = "UPDATE users.profile SET firstName = :firstName, lastName = :lastName, levelId = :levelId WHERE id = :profileId";
            $stmt = $this->db->prepare($sql);
            $params = ['firstName' => $user->firstName, 'lastName' => $user->lastName, 'levelId' => $user->levelId, 'profileId' => $user->profileId];
            $stmt->execute($params);
        } catch (\PDOException $ex) {
            return false;
        }
        return true;
    }
    /**
     * Counts all the users on the server, given a search filter
     *
     * @param string $filter Search filder
     * @return int Number of users
     */
    public function countUsers($filter)
    {
        $sql = "
        DECLARE @filter nvarchar(MAX) = :filter
        SELECT count(1) as [count]
        FROM users.Account u
            INNER JOIN users.profile p on u.profileId = p.id
            INNER JOIN users.level l on l.id = p.levelId
        WHERE 1=1 and ( p.firstName like @filter or p.lastName like @filter)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":filter", "%$filter%", \PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return (int)$result[0]['count'];
    }

    /**
     * delete user by profile id
     *
     * @param int $userProfileId The profile id
     * @return bool True on success
     */
    public function deleteUser($userProfileId)
    {
        try {
            $sql = "
            declare @profileId int = :userProfileId;
            declare @result int;

            exec @result = [users].[DeleteUser] @profileId = @profileId";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(":userProfileId", $userProfileId, \PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (\PDOException $ex) {
            return false;
        }
    }
}