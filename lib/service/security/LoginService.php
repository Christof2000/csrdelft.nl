<?php


namespace CsrDelft\service\security;


use CsrDelft\common\ContainerFacade;
use CsrDelft\common\Security\JwtToken;
use CsrDelft\common\Security\TemporaryToken;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\entity\security\Account;
use CsrDelft\entity\security\enum\AuthenticationMethod;
use CsrDelft\repository\security\AccountRepository;
use CsrDelft\service\AccessService;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

/**
 * Deze service verteld je dingen over de op dit moment ingelogde gebruiker.
 *
 * @package CsrDelft\service
 */
class LoginService {
	/**
	 * Voorgedefinieerde uids
	 */
	public const UID_EXTERN = 'x999';
	public const UID_CLI = 'x900';
	/**
	 * Sessiesleutels
	 */
	const SESS_UID = '_uid';
	const SESS_AUTHENTICATION_METHOD = '_authenticationMethod';
	const SESS_SUED_FROM = '_suedFrom';
	/**
	 * Cookies
	 */
	const COOKIE_REMEMBER = 'remember';
	/**
	 * @var string Huidige uid als met cli is ingelogd.
	 */
	private static $cliUid = 'x999';
	/**
	 * @var AccountRepository
	 */
	private $accountRepository;
	/**
	 * @var Security
	 */
	private $security;
	/**
	 * @var TokenStorageInterface
	 */
	private $tokenStorage;

	public function __construct(
		Security $security,
		AccountRepository $accountRepository,
		TokenStorageInterface $tokenStorage
	) {
		$this->accountRepository = $accountRepository;
		$this->security = $security;
		$this->tokenStorage = $tokenStorage;
	}

	/**
	 * @param string $permission
	 * @param array|null $allowedAuthenticationMethods
	 *
	 * @return bool
	 */
	public static function mag($permission, array $allowedAuthenticationMethods = null) {
		return ContainerFacade::getContainer()->get(LoginService::class)->_mag($permission, $allowedAuthenticationMethods);
	}

	public function _mag($permission, array $allowedAuthenticationMethdos = null) {
		return AccessService::mag($this->_getAccount(), $permission, $allowedAuthenticationMethdos);
	}

	public function _getAccount() {
		if (MODE == 'CLI') {
			return static::getCliAccount();
		}

		return $this->security->getUser() ?? $this->accountRepository->find(self::UID_EXTERN);
	}

	private static function getCliAccount() {
		$account = new Account();
		$account->email = $_ENV['EMAIL_PUBCIE'];
		$account->uid = self::UID_CLI;
		$account->perm_role = 'R_PUBCIE';

		return $account;
	}

	/**
	 * @return string
	 */
	public static function getUid() {
		if (MODE === 'CLI') {
			return static::$cliUid;
		}

		$account = static::getAccount();

		if (!$account) {
			return self::UID_EXTERN;
		}

		return $account->uid;
	}

	/**
	 * @return Account|false
	 */
	public static function getAccount() {
		return ContainerFacade::getContainer()->get(LoginService::class)->_getAccount();
	}

	/**
	 * @return Profiel|false
	 */
	public static function getProfiel() {
		return ContainerFacade::getContainer()->get(LoginService::class)->_getProfiel();
	}

	private function _getProfiel() {
		return $this->_getAccount()->profiel;
	}

	/**
	 * Indien de huidige gebruiker is geauthenticeerd door middel van een token in de url
	 * worden Permissies hierdoor beperkt voor de veiligheid.
	 * @return string|null uit AuthenticationMethod
	 * @see AccessService::mag()
	 */
	public function getAuthenticationMethod() {
		if (MODE == 'CLI') {
			return AuthenticationMethod::password_login;
		}

		$token = $this->security->getToken();

		if ($token == null) {
			return null;
		}

		switch (get_class($token)) {
			case SwitchUserToken::class:
			case TemporaryToken::class:
				$method = AuthenticationMethod::temporary;
				break;
			case UsernamePasswordToken::class:
			case PostAuthenticationToken::class:
				$method = AuthenticationMethod::recent_password_login;
				break;
			case RememberMeToken::class:
			case JwtToken::class:
				$method = AuthenticationMethod::cookie_token;
				break;
			default:
				$method = null;
				break;
		}

		return $method;
	}

	/**
	 * Maak de gebruiker opnieuw recent ingelogd
	 */
	public function setRecentLoginToken() {
		$token = $this->security->getToken();

		if ($token instanceof RememberMeToken) {
			$this->tokenStorage->setToken(
				new UsernamePasswordToken($token->getUser(), [], $token->getProviderKey(), $token->getRoleNames()));
		}
	}
}
