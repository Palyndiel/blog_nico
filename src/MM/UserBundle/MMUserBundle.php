<?php

namespace MM\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class MMUserBundle extends Bundle
{
	public function getParent()
	{
		return 'FOSUserBundle';
	}
}
