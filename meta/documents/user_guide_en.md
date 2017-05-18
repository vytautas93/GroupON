# Groupon marketplace plugin
 
First of all, what is Groupon?
 
It´s a really interesting marketplace for deals :-)
 
Where I have to register?
https://www.groupon.de/merchant/marketing-solutions/goods -> ** Get Started **
 
## What do I have to do, that everything runs perfect and I do not have to write some posts into the forum?
 
1. Register in Groupon (as described above)
2. You will get the following accounts from Groupon:
- https://www.groupon.de/merchant/ <- This is the Merchant Center for ratings and billing
- https://deal-centre.groupon.de/ <- Here you enter your deals!
- https://scm.commerceinterface.com/accounts/ Here you get your SupplierID and the token for the plugin after the first sale!
> These are then in the plugin under Settings under the marketplace, for example DE / EN,...
3. You add "Groupon" under "Settings> Orders> Order origin" and enter the ID in the plugin under Settings> Origin ID
4. Now you select a payment type with which the orders are to be imported, e.g. 1700 for coupon (but activate before) and enters this in the plugin under Settings> Zahlart ID
 
OK to confirm the shipping at Groupon you have to add a event,....
5. Create an event action in "Settings > Orders > events" for your shipped orders!
(for example, add change State to 7.0, filter origin "Groupon" and actions Plugins "Send shipping confirmation to Groupon"
 
### So, what´s next?
 
Well, you have to start a deal at Groupon!
Add a deal to your Groupon Merchant Center with everything you need, and then .... you get a SKU from Groupon!
This SKU you have to enter into your variant in the **SKU selection**. Important, the origin must be the same like your selected orgin in plugin settings ** "Groupon" **!
 
Here is the manual from Groupon:
https://groupon.s3.amazonaws.com/seimages/guide/DealCentre/DE_DealCentre_MerchantManual_ENv2.pdf
 
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