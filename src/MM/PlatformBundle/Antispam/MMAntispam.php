<?php
// src/MM/PlatformBundle/Antispam/MMAntispam.php

namespace MM\PlatformBundle\Antispam;

class MMAntispam
{
	/**
   * Vérifie si le texte est un spam ou non
   *
   * @param string $text
   * @return bool
   */
  public function isSpam($text)
  {
    return strlen($text) < 50;
  }
}