<?php
 
namespace GroupON\Providers;
 
use Plenty\Plugin\ServiceProvider;
use GroupON\Contracts\GroupOnRepositoryContract;
use GroupON\Repositories\GroupOnRepository;

use GroupON\Methods\GroupOnPickupDataMethod;
 
/**
 * Class ToDoServiceProvider
 * @package ToDoList\Providers
 */
class GroupOnServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->getApplication()->register(GroupOnRouteServiceProvider::class);
        $this->getApplication()->bind(GroupOnRepositoryContract::class, GroupOnRepository::class);
    }
    
    public function boot()
    {
        
    }
    
}