# Groupon marketplace plugin
 
First of all, what is Groupon?
 
It´s a marketplace :-)
 
Where I have to register?
https://www.groupon.de/merchant/marketing-solutions/goods -> ** Get Started **
 
## What do I have to do, that everything runs perfect and I do not have to write some posts into the forum?
 
That´s easy :-)
1. You register your store at Groupon (as described above)
2. You get an account from Groupon where you can see the dealer ID and the token
> These are then in the plugin under Settings under the marketplace. DE
3. You add "Groupon" in "Settings > Orders > Order origin" and enter the ID in the plugin Settings > Origin ID
4. Now you select a payment type where you will import the orders with. e.g. 1700 for coupon (but activate this before) and take this ID to the Settings > Payment ID
 
OK to confirm the shipping at Groupon you have to add a event,....
5. Create an event action in "Settings > Orders > events" for your shipped orders!
(for example, add change State to 7.0, filter origin "Groupon" and actions Plugins "confirm Shipping at Groupon"
 
### So, what´s next?
 
Well, you have to start a deal at Groupon!
Add a deal to your Groupon Merchant Center with everything you need, and then .... you get a SKU from Groupon!
This SKU you have to enter into your variant in the **SKU selection**. Important, the origin must be the same like your selected orgin in plugin settings ** "Groupon" **!
 
#### What you should know
 
If you realize that there are no created orders, the following reasons can be the problem:
1. There are no orders at Groupon
2. There are orders but you did not enter the SKU into the variant
3. There are orders but you have the SKU wrongly registered
4. There are orders but the registered SKU is registered to the wrong origin
5. Combinations for the reasons 1-4 in different forms :-p
 
## Currently this is the public beta version!
** We limit this version currently per shop to a free use of one month **
 
There are various reasons for this and the most important is, Groupon has no idea how they will pay us for this plugin!
It´s possible we get full paid by Groupon or we have to adjust it later on.
 
## License
All allows as long as it stays in your system and you are not trying to crack it ;-)