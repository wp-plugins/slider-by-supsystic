<?php


class SupsysticSlider_Installer_Controller extends SupsysticSlider_Core_BaseController
{
    public function askUninstallAction()
    {
        $request = $this->getRequest();

        //Uncomment to enable deactivation dialog
        /*if ($request->query->has('drop')) {
            if ('Yes' == $request->query->get('drop')) {
                return true;
            } else {
                return false;
            }
        }

        return $this->getEnvironment()
            ->getTwig()
            ->render(
                '@installer/uninstall.twig',
                array(
                    'request' => $this->getRequest(),
                )
            );*/

        return false;
    }
} 