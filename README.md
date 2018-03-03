#  Data Navigator

**Data Navigator** a web application that uses *ReactJS* to filter and display JSON data pulled from a web service. Reading this documentation along side working with the examples is probably the best way to understand how everything works.

##  Requirements

This project requires [ReactJS](https://facebook.github.io/react/) to function. Additionally, [Bootstrap](http://getbootstrap.com/)'s CSS is used for the default styling and for some UI JavaScript. [jQuery](http://jquery.com/) is used for some functions as well. The core web application is client-side and can work with any server-side code that accepts and returns JSON. The example web services use PHP.

##  Examples

The **Data Navigator** is already accompanied by an example web service, but in addition there are two larger examples that query databases instead of just using JSON objects created in the service itself.

* **Data Navigator**: The main files - *navigator.js* and *webService.php* - simply show the basic functionality and how a web service might be written to work with the client. The data here is simply some of the contents of this project.
* **Pok&eacute;mon database**: *example-pokemon.js*, *example-pokemon.php*, and *example-pokemon.sql* are an example that allow the user to filter Pok&eacute;mon by various properties such as their type and the generation they first appeared in. This example uses a MySQL database with several tables to obtain data. A live example can probably be found [here](http://hauntedbees.com/test/pokemonTest.html), as of August 7, 2016.
* **World Sexual Terminology Resource**: *example-linguistics.js* and *example-linguistics.php* represent the majority of the [**World Sexual Terminology Resource**](http://hauntedbees.com/ling/index.html), which is the main reason this project exists. The resource serves as a live version of the example, so no setup is required to see the **Data Navigator** in action.

## Structure

The two main files are *index.html* and *navigator.js*. The index file contains the required JavaScript and CSS imports and minimal HTML. The majority of the structure is created using *ReactJS* and is found in the *navigator.js* file.

### Components

#### FullContainer

The **FullContainer** Component holds the three main sections of the application: the **SearchBar**, the **SideBar**, and the **ResultDisplayBox**.

#### SideBar

The **SideBar** contains one or more **SideBarModules**, which are used to filter results. For example, if the web application were used to search cars, modules could be make, model, and year. Each **SideBarModule** contains multiple **SideBarListItems** which, in the car example, would be the makes, models, and years themselves. Clicking **SideBarListItems** will relay this information to the **FullContainer** where they will be processed and the filtering will be done.

Each **SideBarModule** receives its data from a JSON web service which will be described in the Web Services section.

#### SearchBar

The **SearchBar** holds both a text area for user input and the **Breadcrumb**. The text area allows users to search with an arbitrary string in addition to the filters in the **SideBar**. The **Breadcrumb** is a container for the **BreadcrumbItems**, which identify which search query and filters are currently selected and being displayed.

#### ResultDisplayBox

The **ResultDisplayBox** displays either a welcome message, a "no results found" message, or one or more **SingleValues**. The **SingleValue** component is what contains the actual data based on the filters and search query specified. The HTML returned by this Component's render method will likely be changed to suit the data being returned.

In addition to displaying returned data, it also has support to link to related data - clicking on a properly set up link will create another **SingleValue** below it with that data's information. **SingleValues** can also have **SourceButtons** attached to them that, when clicked, will expand the **SingleValue** to provide more information about it.

### Options

At the top of the *navigator.js* file is the *options* object, which, in addition to the HTML returned in the **SingleValue** component, is what will be changed in setting up the application.

#### openingHTML

When the page is first loaded, this HTML will be displayed in the **ResultDisplayBox**.

#### noResultsHTML

If a search query is entered that returns no results (when paired with the filters, or, if there are no filters, on its own), this HTML will be displayed in the **ResultDisplayBox**.

#### searchBreadcrumbClass

All of the **BreadcrumbItems** have a CSS class assigned to them based on the options. Individual filters have this class assigned in the *sidebars* array, described below. The **BreadcrumbItem** created by the user search will use this property for its class.

#### sidebars

This is an array describing all of the filters that will have **SideBarModules** created for them. Each element in the array is a JavaScript object with the following properties:

* **name**: The text that will appear at the top of the **SideBarModule** describing this filter.
* **breadcrumbName**: The text that will appear when this filter is in a **BreadcrumbItem**.
* **breadcrumbClass**: The class that will be given to **BreadcrumbItems** based on this filter.
* **filterType**: The name of this filter to pass to web services. Filters are passed in an array of objects with "key" and "value" properties. This is the "key."
* **getUrl**: The path to the web service that will populate the **SideBarModule** for this filter.

#### contentURL

The path to the web service that will populate the **ResultDisplayBox**.

#### contentSingleURL

When **SingleValues** have child, parent, sibling, or other related data, a web service will be called to get just the data for that related data instead of doing a search like the web service in *contentURL*. This property is the path to that web service.

#### sourcesURL

An optional path to the web service that will pull sources that would be used by **SingleValues** and **SourceButtons**. If left blank, source buttons should not be used.

#### collapseSidebars

If "none", all **SideBarModules** will be expanded on page load. If "all", they will all be collapsed. If "first", then the first **SideBarModule** will be expanded, and the rest will be collapsed. Any other values are interpreted as "none."

### Web Services

This web application uses three web services or functions, plus one for each **SideBarModule**. All web services return a JSON object with two values: "success" and "result." "success" is a boolean that is true when the web service executed successfully. "result" contains another JSON object or array of JSON objects, the contents of which vary depending on which web service it is.

#### Source Gather

The path to this web service is stored in *sourcesURL* in the *options* object. It is called once when the page first loads and its results are stored in the **FullContainer**'s state variable. Unlike most of the other services, this service takes no input and is a "GET" request. Its result object is an array of JSON objects. The contents of these may be changed during setup to whatever is needed in the end result.

#### Content Getter

The path to this web service is stored in *contentURL* in the *options* object. Whenever a search is input or a filter is applied/removed, this service is called to populate the **ResultDisplayBox**. It uses "POST" and takes the "filters" JSON array, described below, as input. Its result object is an array of JSON objects. The contents of these may be changed during setup to whatever is needed in the end result.

#### SideBarModule Getters

Each object in the *sidebars* array in the *options* object has a *getUrl* value, which is the path to this web service. On the page's first load, this is called with an empty "filters" JSON array as input to populate the **SideBarModules**. Whenever a search is performed or a filter is changed, the **SideBarModules** are updated again with this web service. Like the **Content Getter**, it uses "POST." Its result object is an array of JSON objects that have the following key value pairs:

* **code**: The value for this filter - when filtering by this item, this will be what is passed in the "filters" JSON array.
* **name**: The display name for this filter, which will be shown in the **SideBarListItem**.
* **count**: The number of items that match this filter. This number will be displayed next to the name in the **SideBarListItem**.
* **id**: Used by **ReactJS** to ensure each **SideBarListItem** created has a unique key.

##### "filters" JSON

The "filters" JSON array is an array of objects - each one has two properties: *key* and *value.* The *key* will be the **filterType** from the **SideBarModule**'s accompanying object in the *options* object's *sidebars* array, and the *value* will be the *code* of the selected **SideBarListItem**.

#### Single Item Getter

The path to this web service is stored in *contentSingleURL** in the *options* object. When a **SingleValue** has a child, parent, sibling, or other related data, this web service will be called to get information about this object. It is called using "GET" with the **id** of the relevant data as its input. The result will be a single-valued array containing an object with the same format as the objects returned in the **Content Getter** service.
