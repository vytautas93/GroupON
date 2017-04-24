<?php
 
namespace GroupON\Providers;
 
use Plenty\Log\Services\ReferenceContainer;
use Plenty\Plugin\ServiceProvider;

use Plenty\Modules\EventProcedures\Services\EventProceduresService;
use Plenty\Modules\EventProcedures\Services\Entries\ProcedureEntry;
use Plenty\Plugin\Log\Loggable;

use Plenty\Modules\Cron\Services\CronContainer;
use GroupON\Crons\SynchronizeGroupOnOrdersCron;

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
    
    public function boot(
        CronContainer $container,
        EventProceduresService $eventProceduresService,
        ReferenceContainer $referenceContainer
    )
    {
        /**
        * Register Cron Jobs.
        */
        $cron = $container->add(CronContainer::EVERY_FIFTEEN_MINUTES,SynchronizeGroupOnOrdersCron::class);
    
        /**
        * Create Procedure in Settings/Orders/EventProcedures
        */
        
        $eventProceduresService->registerProcedure('SendFeedBack',ProcedureEntry::PROCEDURE_GROUP_ORDER, [
            'de' => 'Send Feedback DE',
            'en' => 'Send Feedback EN'

        ], 'GroupON\\Controllers\\ContentController@Procedure');
    }
}