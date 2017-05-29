<?php
namespace App\Service;

//use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

class Twig extends \Twig_Extension
{
    private $router;
    private $translator;
    private $security;
    //private $kernel;
    private $params = [];

    public function __construct(
        $app_version,
        Router $router,
        TokenStorage $security,
        //Kernel $kernel,
        Translator $translator
    ) {
        $this->router = $router;
        $this->translator = $translator;
        $this->security = $security;
        //$this->kernel = $kernel;
        $this->params['app_version'] = $app_version;
    }

    public function getFilters()
    {
        return array(
            'routeClass' => new \Twig_SimpleFilter('routeClass', [$this, 'routeClassFilter']),
            'routeExists' => new \Twig_SimpleFilter('routeExists', [$this, 'routeExistFilter']),
            'assetVersion' => new \Twig_SimpleFilter('assetVersion', [$this, 'assetVersionFilter']),
            'fileExtension' => new \Twig_SimpleFilter('fileExtension', [$this, 'fileExtensionFilter']),
            'age' => new \Twig_SimpleFilter('age', [$this, 'ageFilter'])
        );
    }

    public function routeClassFilter($route)
    {
        $classname = $route;
        $routeExplode = explode('_', $route);

        if (count($routeExplode) > 2) {
            $classname .= ' ' . $routeExplode[0] . '_' . $routeExplode[1];
        }

        return $classname;
    }

    public function routeExistsFilter($name)
    {
        return (null === $this->router->getRouteCollection()->get($name)) ? false : true;
    }

    /*public function assetVersionFilter($url)
    {
        return $url . '?v=' . ($this->kernel->getEnvironment() == 'dev' ? time() : $this->params['app_version']);
    }*/

    public function fileExtensionFilter($filepath)
    {
        return pathinfo($filepath, PATHINFO_EXTENSION);
    }

    public function ageFilter($date)
    {
        $now = new \DateTime();
        $interval = $now->diff(new \DateTime($date));

        return $interval->y;
    }

    public function getName()
    {
        return 'twig_extension';
    }
}
