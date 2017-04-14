<?php
 
namespace GroupON\Providers;
 
use Plenty\Log\Services\ReferenceContainer;
use Plenty\Plugin\ServiceProvider;
use Plenty\Modules\Cron\Services\CronContainer;
use GroupON\Crons\SynchronizeGroupOnOrdersCron;
use Plenty\Modules\EventProcedures\Services\EventProceduresService;
 
/**
 * Class GroupOnServiceProvider
 * @package GroupON\Providers
 */
class GroupOnServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->getApplication()->register(GroupOnRouteServiceProvider::class);
    }
    
     public function boot(CronContainer $container,EventProceduresService $eventProceduresService,ReferenceContainer $referenceContainer)
     {
         $container->add(CronContainer::EVERY_FIFTEEN_MINUTES,SynchronizeGroupOnOrdersCron::class);
     }
}