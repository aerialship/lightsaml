<?php

namespace AerialShip\LightSaml\Command;

use AerialShip\LightSaml\Bindings;
use AerialShip\LightSaml\Meta\SerializationContext;
use AerialShip\LightSaml\Model\Metadata\EntityDescriptor;
use AerialShip\LightSaml\Model\Metadata\KeyDescriptor;
use AerialShip\LightSaml\Model\Metadata\Service\AssertionConsumerService;
use AerialShip\LightSaml\Model\Metadata\Service\SingleLogoutService;
use AerialShip\LightSaml\Model\Metadata\SpSsoDescriptor;
use AerialShip\LightSaml\Security\X509Certificate;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class BuildSPMetadataCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('aerialship:lightsaml:sp:meta:build')
            ->setDescription('Build SP metadata xml')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var  $dialog DialogHelper */
        $dialog = $this->getHelperSet()->get('dialog');

        $entityID = $dialog->askAndValidate($output, 'EntityID [https://example.com/saml]: ', function($answer) {
            $answer = trim($answer);
            if (!$answer) {
                throw new \RuntimeException('EntityID can not be empty');
            }
            return $answer;
        }, false, 'https://example.com/saml');

        $ed = new EntityDescriptor($entityID);

        $certificatePath = $this->askFile($dialog, $output, 'Signing Certificate path', false);
        if ($certificatePath) {
            $certificate = new X509Certificate();
            $certificate->loadFromFile($certificatePath);
            $keyDescriptor = new KeyDescriptor('signing', $certificate);
            $ed->addItem($keyDescriptor);
        }

        $sp = new SpSsoDescriptor();
        $ed->addItem($sp);

        $output->writeln('');

        $wantAssertionsSigned = (bool)$dialog->select($output, 'Want assertions signed [yes]: ', array('no', 'yes'), 1);
        $sp->setWantAssertionsSigned($wantAssertionsSigned);

        $output->writeln('');

        while (true) {
            list($url, $binding) = $this->askUrlBinding($dialog, $output, 'Single Logout');
            if (!$url) {
                break;
            }
            $s = new SingleLogoutService();
            $s->setLocation($url);
            $s->setBinding($this->resolveBinding($binding));
            $sp->addService($s);
            break;
        }

        $output->writeln('');

        $index = 0;
        while (true) {
            list($url, $binding) = $this->askUrlBinding($dialog, $output, 'Assertion Consumer Service');
            if (!$url) {
                break;
            }
            $s = new AssertionConsumerService($this->resolveBinding($binding), $url, $index++);
            $sp->addService($s);
        }

        $output->writeln('');

        $filename = $dialog->askAndValidate($output, 'Save to filename [FederationMetadata.xml]: ',
            function($answer) {
                $answer = trim($answer);
                if (!$answer) {
                    throw new \RuntimeException('Filename can not be empty');
                }
                return $answer;
            },
            false, 'FederationMetadata.xml'
        );

        $formatOutput = $dialog->select($output, 'Format output xml [no]: ', array('no', 'yes'), 0);

        $context = new SerializationContext();
        $context->getDocument()->formatOutput = (bool)$formatOutput;
        $ed->getXml($context->getDocument(), $context);
        $xml = $context->getDocument()->saveXML();
        file_put_contents($filename, $xml);
    }


    protected function resolveBinding($binding) {
        switch ($binding) {
            case 'post':
                return Bindings::SAML2_HTTP_POST;
            case 'redirect':
                return Bindings::SAML2_HTTP_REDIRECT;
            default:
                throw new \RuntimeException("Unknown binding $binding");
        }
    }

    protected function askFile(DialogHelper $dialog, OutputInterface $output, $title, $required) {
        $result = $dialog->askAndValidate($output, "$title [empty for none]: ",
            function($answer) use ($required) {
                if (!$required && !$answer) {
                    return null;
                }
                if (!is_file($answer)) {
                    throw new \RuntimeException('Specified file not found');
                }
                return $answer;
            }
        );
        return $result;
    }

    protected function askUrlBinding(DialogHelper $dialog, OutputInterface $output, $title) {
        $url = $dialog->ask($output, "$title URL [empty for none]: ");
        $url = trim($url);
        if (!$url) {
            return array(null, null);
        }

        $arrBindings = array('post', 'redirect');
        $binding = $dialog->select($output, 'Binding: ', $arrBindings, 'post');
        $binding = $arrBindings[$binding];

        return array($url, $binding);
    }

} 