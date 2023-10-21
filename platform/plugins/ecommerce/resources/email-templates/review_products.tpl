{{ header }}

<h2>Order completed!</h2>

<p>Hi {{ customer_name }},</p>
<p>Thank you for purchasing our products, you can review the product below!</p>

{{ product_review_list }}

<br />

<p>If you have any question, please contact us via <a href="mailto:{{ site_admin_email }}">{{ site_admin_email }}</a></p>

{{ footer }}
