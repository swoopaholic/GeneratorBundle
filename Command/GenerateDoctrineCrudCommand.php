<?php
/*
 * (c) Netvlies Internetdiensten
 *
 * author Danny Dörfel <ddorfel@netvlies.nl>
 * date: 23-5-13 20:22
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Swoopaholic\Bundle\GeneratorBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Mapping\DisconnectedMetadataFactory;
use Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineCrudCommand as Base;
use Swoopaholic\Bundle\GeneratorBundle\Generator\DoctrineCrudGenerator;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class GenerateDoctrineCrudCommand extends Base
{
    protected $generator;
//    protected $formGenerator;

    protected function configure()
    {
        parent::configure();

        $this->setName('swp:generate:doctrine:crud');
        $this->setDescription('Our admin generator swooooops it like he stole it!');
    }

    protected function getGenerator(BundleInterface $bundle = null)
    {
        if (null === $this->generator) {
            /** @noinspection PhpParamsInspection */
            $this->generator = new DoctrineCrudGenerator(
                $this->getContainer()->get('filesystem')
            );
        }

        // TODO: set from config
        $this->generator->setSkeletonDirs(
            array(
                $this->getContainer()->get('kernel')->locateResource('@NvsRavenLayoutBundle/Resources/skeleton/'),
                $this->getContainer()->get('kernel')->locateResource('@NvsRavenLayoutBundle/Resources/skeleton/crud'),
            )
        );

        return $this->generator;
    }

    protected function getEntityMetadata($entity)
    {
        /** @noinspection PhpParamsInspection */
        $factory = new DisconnectedMetadataFactory($this->getContainer()->get('doctrine'));
        $metadata =  $factory->getClassMetadata($entity)->getMetadata();

        /** @var \Doctrine\Bundle\DoctrineBundle\Registry $doctrine */
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();

        $allMetaData = $em->getMetadataFactory()->getAllMetadata();

        foreach ($allMetaData as $item) {
            if ($item->name == $metadata[0]->name) {

                $metadata = $item;
                $ids = $metadata->identifier;

                foreach ($ids as $id) {
                    $column = $metadata->fieldMappings[$id];
                    unset($metadata->fieldMappings[$id]);
                    $metadata->fieldMappings = array_merge(array($id => $column), $metadata->fieldMappings);
                }
                return array($metadata);
            }
        }

        return $metadata;
    }
}
