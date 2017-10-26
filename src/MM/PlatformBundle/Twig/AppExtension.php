<?php
namespace MM\PlatformBundle\Twig;

class AppExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('since', array($this, 'sinceFilter')),
        );
    }

    public function sinceFilter($date)
    {
        $now = new \DateTime();
        $dif = $now->diff($date);
        $minute = new \DateInterval('PT60S');
        $heure = new \DateInterval('PT1H');
        $jour = new \DateInterval('PT24H');
        $mois = new \DateInterval('P1M');
        
        if ((array)$dif < (array)$minute){
            $result = $dif->format('il y a %s secondes');
        }
        elseif ((array)$dif < (array)$heure){
            $result = $dif->format('il y a %i minutes');
        }
        elseif ((array)$dif < (array)$jour){
            $result = $dif->format('il y a %h heures');
        }
        elseif ((array)$dif < (array)$mois){
            $result = $dif->format('il y a %d jours');
        }
        else {
            $result = $dif->format('il y a %m mois');
        }

        return $result;
    }

    public function getName()
    {
        return 'app_extension';
    }
}