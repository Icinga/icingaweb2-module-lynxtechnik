<?php

class LConf_Command extends LConf_Object
{
    protected $class_name = 'Command';

    public static function fromNagiosCommand(Nagios_Command $cmd, LConf_Directory $dir)
    {
        $command = new LConf_Command($cmd->command_name, $dir, array(
            'commandLine' => $cmd->command_line
        ));
        return $command;
    }

}

