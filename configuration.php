<?php

$section = $this->menuSection(N_('LYNX Technik'))->setIcon('sliders');
$section->add(N_('Monitored Services'))->setUrl('lynxtechnik/list/services');
$section->add(N_('Racks and Stacks'))->setUrl('lynxtechnik/show/stack');
