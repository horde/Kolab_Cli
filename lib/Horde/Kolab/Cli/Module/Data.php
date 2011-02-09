<?php
/**
 * The Horde_Kolab_Cli_Module_Data:: class handles Kolab data.
 *
 * PHP version 5
 *
 * @category Horde
 * @package  Kolab_Cli
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=Kolab_Cli
 */

/**
 * The Horde_Kolab_Cli_Module_Data:: class handles Kolab data.
 *
 * Copyright 2011 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @category Horde
 * @package  Kolab_Cli
 * @author   Gunnar Wrobel <wrobel@pardus.de>
 * @license  http://www.fsf.org/copyleft/lgpl.html LGPL
 * @link     http://pear.horde.org/index.php?package=Kolab_Cli
 */
class Horde_Kolab_Cli_Module_Data
implements Horde_Kolab_Cli_Module
{
    /**
     * Get the usage description for this module.
     *
     * @return string The description.
     */
    public function getUsage()
    {
        return Horde_Kolab_Cli_Translation::t("  data - Handle Kolab data (the default action is \"info\"). PATH refers to the path of the folder that holds the data and the optional TYPE argument indicates which data type should be read. This is usually already defined by the folder setting.

  - info      PATH  : Display general information.
  - status    PATH  : Display the folder status.


");
    }

    /**
     * Get a set of base options that this module adds to the CLI argument
     * parser.
     *
     * @return array The options.
     */
    public function getBaseOptions()
    {
        return array();
    }

    /**
     * Indicate if the module provides an option group.
     *
     * @return boolean True if an option group should be added.
     */
    public function hasOptionGroup()
    {
        return false;
    }

    /**
     * Return the title for the option group representing this module.
     *
     * @return string The group title.
     */
    public function getOptionGroupTitle()
    {
        return '';
    }

    /**
     * Return the description for the option group representing this module.
     *
     * @return string The group description.
     */
    public function getOptionGroupDescription()
    {
        return '';
    }

    /**
     * Return the options for this module.
     *
     * @return array The group options.
     */
    public function getOptionGroupOptions()
    {
        return array();
    }

    /**
     * Handle the options and arguments.
     *
     * @param mixed &$options   An array of options.
     * @param mixed &$arguments An array of arguments.
     * @param array &$world     A list of initialized dependencies.
     *
     * @return NULL
     */
    public function handleArguments(&$options, &$arguments, &$world)
    {
    }

    /**
     * Run the module.
     *
     * @param Horde_Cli $cli       The CLI handler.
     * @param mixed     $options   An array of options.
     * @param mixed     $arguments An array of arguments.
     * @param array     &$world    A list of initialized dependencies.
     *
     * @return NULL
     */
    public function run($cli, $options, $arguments, &$world)
    {
        if (!isset($arguments[1])) {
            $action = 'info';
        } else {
            $action = $arguments[1];
        }
        if (!isset($arguments[2])) {
            $folder_name = 'INBOX';
        } else {
            $folder_name = $arguments[2];
        }
        switch ($action) {
        case 'info':
            break;
        case 'status':
            $data = $world['storage']->getData($folder_name);
            $status = $data->getStatus();
            $pad = max(array_map('strlen', array_keys($status))) + 2;
            foreach ($status as $key => $value) {
                $cli->writeln(Horde_String::pad($key . ':', $pad) . $value);
            }
            break;
        case 'uids':
            $data = $world['storage']->getData($folder_name);
            $uids = $data->getUids();
            foreach ($uids as $uid) {
                $cli->writeln($uid);
            }
            break;
        case 'structure':
            $data = $world['storage']->getData($folder_name);
            $messages = $data->fetchStructure(explode(',', $arguments[3]));
            foreach ($messages as $message) {
                $this->_messageOutput(
                    $cli, $message['uid'], $message['structure']->toString()
                );
            }
            break;
        case 'part':
            $data = $world['storage']->getData($folder_name);
            $part = $data->fetchBodypart($arguments[3], $arguments[4]);
            rewind($part[$arguments[3]]['bodypart'][$arguments[4]]);
            $cli->writeln(
                stream_get_contents(
                    $part[$arguments[3]]['bodypart'][$arguments[4]]
                )
            );
            break;
        case 'fetch':
            $data = $world['storage']->getData($folder_name, $arguments[3]);
            $objects = $data->fetch(explode(',', $arguments[4]));
            foreach ($objects as $uid => $message) {
                if (class_exists('Horde_Yaml')) {
                    $this->_messageOutput($cli, $uid, Horde_Yaml::dump($message));
                } else {
                    $this->_messageOutput($cli, $uid, print_r($message, true));
                }
            }
            break;
        default:
            $cli->message(
                sprintf(
                    Horde_Kolab_Cli_Translation::t('Action %s not supported!'),
                    $action
                ),
                'cli.error'
            );
            break;
        }
    }

    private function _messageOutput($cli, $uid, $output)
    {
        $cli->writeln('Message UID [' . $uid . ']');
        $cli->writeln('================================================================================');
        $cli->writeln();
        $cli->writeln($output);
        $cli->writeln();
        $cli->writeln('================================================================================');
        $cli->writeln();
    }
}