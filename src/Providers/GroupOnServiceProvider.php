<?php
 
namespace GroupON\Providers;
 
use Plenty\Log\Services\ReferenceContainer;
use Plenty\Plugin\ServiceProvider;
use Plenty\Modules\Cron\Services\CronContainer;
use GroupON\Crons\SynchronizeGroupOnOrdersCron;
use Plenty\Modules\EventProcedures\Services\EventProceduresService;
 

use Plenty\Plugin\Log\Loggable;
use Plenty\Plugin\Events\Dispatcher;
use Plenty\Modules\Order\Events\OrderCreated; 
/**
 * Class GroupOnServiceProvider
 * @package GroupON\Providers
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
    }
    
/*     public function boot(CronContainer $container,EventProceduresService $eventProceduresService,ReferenceContainer $referenceContainer)
     {
         $container->add(CronContainer::EVERY_FIFTEEN_MINUTES,SynchronizeGroupOnOrdersCron::class);
     }*/

    public function boot(Dispatcher $dispatcher)
    {
        $test = $dispatcher->listen(OrderCreated::class,function()
        {
            
            $this->getLogger(__FUNCTION__)->error('Order Created and works', "Order Created and works");  
            
        });
    }
}