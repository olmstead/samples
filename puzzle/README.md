Puzlr: A social image based mobile game
---------------------------------------

Puzlr is a mobile application in progress.  Since it is a fully self-created project, it serves as a
good example of the kind of technology and code that excites me.  Rather than post the entire project, I have included
code samples that I hope illustrate the simplicity and potential power of the architecture.

The client is a mobile app for IOS and Android that is built using the cross-platform Titanium framework from 
Appcelerator.  The server is built in PHP using Symfony with MongoDB as the data store. 

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
![Screen shot](http://farm3.staticflickr.com/2832/9558407205_99c8fd2598_m.jpg "")&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
![Screen shot](http://farm8.staticflickr.com/7451/9558407309_b3f3abc7de_m.jpg "")&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
![Screen shot](http://farm4.staticflickr.com/3763/9561193304_d665eedee8_m.jpg "")

Mobile Client
-------------
The mobile client is built using a new client side MVC framework for Titanium called Alloy that is built using 
Backbone.  The Appcelerator tools are a great match for this project because they give fairly easy access to the 
high performance native ListView component implementation on both the Android and IOS platforms in a Javascript 
development environment.  The ListView component is used to display large lists of puzzles with lazy loading of content.

The alloy framework has allowed me to rapidly create some powerful features when tied to a backend services, and Backbone
acts as the glue that really makes that happen. 

Data is passed as JSON bewteen the mobile client and REST based services.  

Server Application
-----------------
The server component is built using the Symfony framework for PHP.  Symfony provides an excellent security layer based
on Java spring framework security where I can mix and match types of authentication.  Symfony also provides an MVC that
is based largely on Rails and which is highly efficient and customizable.  

Persistence
------------
Data persistence is implemented using the Doctrine ODM to connect to a MongoDB store.  Monogo was a good fit as the
data stored does not require complex relationships and it made for rapid development without the need to version
schemas.  

Image files are stored at Amazon S3 on upload and are served to mobile devices via the cloud which means the app
server only needs to serve up data.

The code below illustrates persisting data taken from a form as two document types and storing the image
binary using Amazon S3.
```php
	// create a new puzzle model 
        $puzzle = new Puzzle();
        $form = $this->createForm(new PuzzleType(), $puzzle);
        
        // process posted form data
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                // get the uploaded image 
                $upload = $puzzle->getImageFile();
                
                // get the amazon s3 base url
                $host = $this->container->getParameter('jpo_storage.amazon_s3.base_url');
                
                // upload the file to s3 and get the filename
                $filename = $this->getPhotoUploader()->upload($upload);
                
                // build the s3 url for the ikmaage
                $url = "http://$host/$filename";
                
                // create persistent storage for the image and save in mongo
                $image = new Image($url, $upload->getClientOriginalName());
                $puzzle->setImage($image);
                $dm = $this->get('doctrine.odm.mongodb.document_manager');
                $dm->persist($image);
                $dm->persist($puzzle);
                $dm->flush();
            } 
       }
```

Security
---------
For security users can login via Facebook or register as a user of the app.  This is handled in Symfony using
a security provider chaining feature.  Since the Facebook connection is established using the Titanium libraries
on the client, there has been considerable customization work required to facilitate passing of OAuth credentials to the server.
        
```php
	$user = $this->findUserByFbId($username);
        // get fb user data
        try {
            $fbUserData = $this->facebook->api('/me');
        } catch (FacebookApiException $e) {
            $fbUserData = null;
        }

        if (!empty($fbUserData)) {
            // create a new user if needed
            if (empty($user)) {
                $user = $this->userManager->createUser();
                $user->setEnabled(true);
                $user->setPassword('');
            }
            // set the fb data forr the user
            $user->setFBData($fbUserData);

            if (count($this->validator->validate($user, 'Facebook'))) {
                // TODO: the user was found obviously, but doesnt match our expectations, do something smart
                throw new UsernameNotFoundException('The facebook user could not be stored');
            }
            $this->userManager->updateUser($user);
        }

        if (empty($user)) {
            throw new UsernameNotFoundException('The user is not authenticated on facebook');
        }
```

Puzzle list Fetch
------------
Below I follow how a list of photo puzzles from the client implementation to the server implementation.

Here is the view and model declaration that uses a separate template for each item. Note that the declaration includes 
the binding of the data model collection(id="puzzles") to the ListSection via $.puzzles.  
```xml
<Alloy>
    <Collection id="puzzles" src="puzzle" instance="true" />
    <View  id="puzzle_list_wrapper">
        <ActivityIndicator id="activityIndicator" />
        <Label id="labelNoRecords" />
        <ListView id="puzzle_list" onItemclick="onPuzzleClick" defaultItemTemplate="itemTemplate">
            <Templates>
                <Require src="itemTemplate"/>
            </Templates>
            <ListSection id="recent_puzzles" dataCollection="$.puzzles">
                <ListItem root:id="{id}" 
                    guesses:text="{guesses}"  
                    days:text="{days}" 
                    points:text="{points}" 
                    imageview:image="{url}"/>
            </ListSection>
        </ListView>	
    </View>
</Alloy>
```

This creates a collection of data models that are bound to the ListView UI.  The data models populate via the server
side REST API.

Below is part of the puzzle model.  I include this to show the simplicity defining model objects in backbone, as well as
to illustrate that it uses my custom REST API adapter via the configured URL

```javascript
exports.definition = {
    config: {
    	//"debug": 1,
        "columns": {
            "id": "TEXT",
            "points": "Integer",
            "name": "TEXT",
            "created": "TEXT",
            "width": "Integer",
            "height": "Integer",
            "url": "TEXT",
            "user_id": "TEXT",
            "user_name": "TEXT",
            "days": "Integer",
            "guesses": "Integer"
        },
        "defaults": {
            "id": null
        },
        "adapter": {
            "type": "restapi",
            "collection_name": "puzzle",
            "idAttribute": "id"
        },
        "URL": Alloy.Globals.puzlsrvr + "/json/puzzle"
	}
}
```

To connect to the server, backbone use a custom adapter.  The adapter implements a Sync method which handles each 
CRUD operation that in turn make an XHR request and handle the response appropriately.

    
On the server side, Symfony uses routing to direct the request to the controller.  The url can contain either
html or json as the format and the app is configured to return the appropriate response.
```yml
puzzle_list:
    pattern:  /{_format}/puzzle
    defaults: 
        _controller: puzlrImageBundle:Puzzle:index 
        _format: html
    requirements:
        _format: html|json 
        _method: GET
```

This directs the request to the index action of the Puzl controler, where currently the entire list
of puzzles found in Mongo is returned.  The plan is to add sorting and filter capability here,
but for the first iteration I wanted to get the end-to-end functionality working first.  I typically like
to work this way, writing code quickly, getting a prototype working, then filling in the details.
```php
    /**
     * @Template()
     */
    public function indexAction()
    {
        $puzzles = $this->get('doctrine.odm.mongodb.document_manager')
            ->getRepository('puzlrImageBundle:Puzzle')
            ->findAll();
        
        // serialize
        $data = array();
        foreach ($puzzles as $puzzle) {
            $data[] = $puzzle->toArray(array('hint', 'answer', 'slug'), array('image'));
        }
        if ($this->getRequest()->getRequestFormat() == 'json') {
            return new JsonResponse($data);     
        }
        // else
        return array (
            'data' => $data, 
            'form' =>  $this->createForm(new GuessType(), new Guess())->createView()
        );
    }
```
Here we fetch the puzzles from mongo, do a simple serialization, and return the response based on the format.
Using Symfony, this could cleaned up by creating a response listener class that would handle the difference in the 
response based on the format so that each action would be simpler.

In the case that the HTML format is requested, a feature I often use for debugging, the @Template annotation tells 
Symfony to render using a template with a matching name, in this case Puzzle/index.html.twig.  Twig, the default 
template engine in Symfony, then renders the data using a very powerful inheritance based template system.

