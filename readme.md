Nette Web Project
=================

Welcome to the Nette Web Project! This is a basic skeleton application built using
[Nette](https://nette.org), ideal for kick-starting your new web projects.

Nette is a renowned PHP web development framework, celebrated for its user-friendliness,
robust security, and outstanding performance. It's among the safest choices
for PHP frameworks out there.

If Nette helps you, consider supporting it by [making a donation](https://nette.org/donate).
Thank you for your generosity!


Requirements
------------

This Web Project is compatible with Nette 3.1 and requires PHP 8.0.


Installation
------------

To install the Web Project, Composer is the recommended tool. If you're new to Composer,
follow [these instructions](https://doc.nette.org/composer). Then, run:

	composer create-project nette/web-project path/to/install
	cd path/to/install

Ensure the `temp/` and `log/` directories are writable.


Web Server Setup
----------------

To quickly dive in, use PHP's built-in server:

	php -S localhost:8000 -t www

Then, open `http://localhost:8000` in your browser to view the welcome page.

For Apache or Nginx users, configure a virtual host pointing to your project's `www/` directory.

**Important Note:** Ensure `app/`, `config/`, `log/`, and `temp/` directories are not web-accessible.
Refer to [security warning](https://nette.org/security-warning) for more details.


Minimal Skeleton
----------------

For demonstrating issues or similar tasks, rather than starting a new project, use
this [minimal skeleton](https://github.com/nette/web-project/tree/minimal).



For JS code implemented in Shoptet:
----------------
`<script>
document.addEventListener("DOMContentLoaded", (event) => {
		if(sessionStorage.setItem("clickViewProduct") == "1"){
    	sessionStorage.setItem("clickViewProduct", "0");
      if(shoptet.customer.guid != null){
        let priceBefore = document.getElementsByClassName("price-final-holder")[0].innerText;
        if(document.getElementsByClassName("price-standard")[0] != null)
            priceBefore = document.getElementsByClassName("price-standard")[0].innerText;
    		fetch('https://www.retentionup-doplnek.cz/api/viewedproduct', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: {id: shoptet.customer.guid, item: {name: document.getElementsByClassName("p-detail-inner-header")[0].childNodes[1].innerText, price: document.getElementsByClassName("price-final-holder")[0].innerText, url: window.location.href, priceBefore: priceBefore, image: document.getElementsByClassName("p-thumbnail highlighted")[0].href } }
        })
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json();
        })
        .then(data => {
          // Handle the response data as needed
          console.log(data);
        })
        .catch(error => {
          // Handle errors
          console.error('Error:', error);
        });
      }
    }
    let allProducts = document.getElementsByClassName("product");
    const productsArray = Array.from(allProducts);

    // Add click event to each element
    productsArray.forEach(product => {
      product.addEventListener('click', function() {
        sessionStorage.setItem("clickViewProduct", "1");
      });
    });
		if(shoptet.customer.guid != null){
    		fetch('https://www.retentionup-doplnek.cz/api/activeonsite', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: {id: shoptet.customer.guid}
    })
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.json();
    })
    .then(data => {
      // Handle the response data as needed
      console.log(data);
    })
    .catch(error => {
      // Handle errors
      console.error('Error:', error);
    });
    }
    if (window.location.pathname.includes("/kosik") || window.location.pathname.includes("/cart")) {
    
    // Sample user_guid value (replace with actual value)
    const userGuid = shoptet.customer.guid;
    if(userGuid != null){
    // Sample items array (replace with actual items)
    const items = [
      { name: "Item1", quantity: 2, price: 10.99 },
      { name: "Item2", quantity: 1, price: 5.99 },
      // Add more items as needed
    ];

    // Create the data object with parameters
    const data = {
      shoptet: {
        customer: {
          guid: userGuid
        }
      },
      items: shoptet.content.initiateCheckoutData
    };

    // Convert data object to JSON
    const jsonData = JSON.stringify(data);

    // Send POST request
    fetch('https://www.retentionup-doplnek.cz/api/startcheckout', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: jsonData
    })
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.json();
    })
    .then(data => {
      // Handle the response data as needed
      console.log(data);
    })
    .catch(error => {
      // Handle errors
      console.error('Error:', error);
    });
  }
  }
});
</script>`