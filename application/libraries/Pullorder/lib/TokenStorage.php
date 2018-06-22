<?php
namespace OAuth\Common\Storage;
// 需注入到 OAuth\Common\Storage 命名空间 实现TokenStorageInterface接口


use OAuth\Common\Token\TokenInterface;
use OAuth\Common\Storage\Exception\TokenNotFoundException;
use OAuth\Common\Storage\Exception\AuthorizationStateNotFoundException;

/**
 *  实现TokenStorageInterface接口
 *  TokenStorage 存储 token
 */
class TokenStorage implements TokenStorageInterface
{
    //根据不同userId save
    protected $userId;
    protected $tokeFile;
    protected $stateFile;


    public function setUserId($userId)
    {
        $this->userId = $userId;
        $this->tokeFile = DATA_DIR."/tokenStorage/".$userId . ".ser";
        $this->stateFile = DATA_DIR."/tokenStorage/".$userId."_state.ser";
    }


    public function __construct(     $userId   ) {

        //$this->userId = $userId;
        //$this->tokeFile = DATA_DIR."/TokenStorage/".$userId.".ser";
        $this->setUserId($userId);
        mkdirp(dirname($this->tokeFile));

    }

    //解出token
    public function retrieveAccessToken($service)
    {
        if (file_exists($this->tokeFile)) {
            return unserialize(file_get_contents($this->tokeFile));
        }

        throw new TokenNotFoundException('Token not found in session, are you sure you stored it?');
    }


    //保存token
    public function storeAccessToken($service, TokenInterface $token)
    {
        $serializedToken = serialize($token);

        file_put_contents($this->tokeFile, $serializedToken);

        /*
        if (isset($_SESSION[$this->sessionVariableName])
            && is_array($_SESSION[$this->sessionVariableName])
        ) {
            $_SESSION[$this->sessionVariableName][$service] = $serializedToken;
        } else {
            $_SESSION[$this->sessionVariableName] = array(
                $service => $serializedToken,
            );
        }
        */

        // allow chaining
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function hasAccessToken($service)
    {
        return file_exists($this->tokeFile);
        //return isset($_SESSION[$this->sessionVariableName], $_SESSION[$this->sessionVariableName][$service]);
    }

    /**
     * {@inheritDoc}
     */
    public function clearToken($service)
    {
        unlinkf($this->tokeFile) ;

        /*
        if (array_key_exists($service, $_SESSION[$this->sessionVariableName])) {
            unset($_SESSION[$this->sessionVariableName][$service]);
        }
        */

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function clearAllTokens()
    {
        //unset($_SESSION[$this->sessionVariableName]);

        // allow chaining
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function storeAuthorizationState($service, $state)
    {
        /*
        if (isset($_SESSION[$this->stateVariableName])
            && is_array($_SESSION[$this->stateVariableName])
        ) {
            $_SESSION[$this->stateVariableName][$service] = $state;
        } else {
            $_SESSION[$this->stateVariableName] = array(
                $service => $state,
            );
        }
        */

        file_put_contents($this->stateFile, $state);

        // allow chaining
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function hasAuthorizationState($service)
    {
        //return isset($_SESSION[$this->stateVariableName], $_SESSION[$this->stateVariableName][$service]);
        return file_exists($this->stateFile);
    }

    /**
     * {@inheritDoc}
     */
    public function retrieveAuthorizationState($service)
    {
        /*
        if ($this->hasAuthorizationState($service)) {
            return $_SESSION[$this->stateVariableName][$service];
        }
        */
        if(file_exists($this->stateFile)){
            return file_get_contents($this->stateFile);
        }

        throw new AuthorizationStateNotFoundException('State not found in session, are you sure you stored it?');
    }


    public function clearAuthorizationState($service)
    {
//        if (array_key_exists($service, $_SESSION[$this->stateVariableName])) {
//            unset($_SESSION[$this->stateVariableName][$service]);
//        }
        return $this;
    }


    public function clearAllAuthorizationStates()
    {
//        unset($_SESSION[$this->stateVariableName]);
        return $this;
    }

    public function __destruct()
    {

    }


    protected function sessionHasStarted()
    {
        return true;
    }
}
