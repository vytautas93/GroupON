<?php
 
namespace GroupON\Providers;
 
use Plenty\Plugin\ServiceProvider;
use Plenty\Modules\Cron\Services\CronContainer;

use Plenty\Plugin\Log\Loggable;
use Plenty\Log\Services\ReferenceContainer;
use Plenty\Modules\EventProcedures\Services\EventProceduresService;
use GroupON\Crons\SynchronizeGroupOnOrdersCron;
use GroupON\Contracts\GroupOnRepositoryContract;
use GroupON\Repositories\GroupOnRepository;
use GroupON\Methods\GroupOnPickupDataMethod;
 
/**
 * Class ToDoServiceProvider
 * @package ToDoList\Providers
 */
class GroupOnServiceProvider extends ServiceProvider
{
    use Loggable;
    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->getApplication()->register(GroupOnRouteServiceProvider::class);
        /*$this->getApplication()->bind(GroupOnRepositoryContract::class, GroupOnRepository::class);*/
    }
    
    public function boot(CronContainer $container,EventProceduresService $eventProceduresService,ReferenceContainer $referenceContainer)
    {
        $container->add(CronContainer::EVERY_FIFTEEN_MINUTES,SynchronizeGroupOnOrdersCron::class);
    }
}