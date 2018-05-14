# Taxi Web Application (Hire Taxi)
This application is developed for a administrator / Support person where he/she can book a taxi for his client/customer who called them over phone.

# Procedure/flow of this application is as below
1. customer will call to Hire Taxi to book a cab.
2. Admin/support person will ask customer's details, exact location, type of cab (Normal / Pink) and check the nearby cabs.
3. As per customers requirement he/she will assign a driver and start the trip.
4. At the end of the trip customer/driver will provide the exact location and end the trip.
5. Booking details will be displayed on the screen after ending the trip.

# Technical details
-> Front End           : HTML, CSS, Bootstrap
-> Frond End Framework : Angualr 5
-> Backend             : PHP 5.7+
-> Database            : MYSQL
-> Web server           : Apache 2

# Technical Workflow.

# FrontEnd
-> Once we start the application Angualr will take control. Angualr 5 will work on component basis (every component will have one typescript .ts file and .html file. .ts file will have the logic and render the data and will pass it to .html file). And Default componenet in this application is App Component (app.component.ts)

# app.component.ts
In this comonent we will check if any admin/support person is logged in or not. if logged in app.component.ts will redirect to dashboard.component.ts. If not logged in it will redirect to login.component.ts

# login.component.ts
In this component it will collect the username, password and pass the details to custom service. All the API calls in this app are been managed by a custom service(api.service.ts). custom service will retun the response. If the response is true then the username and user tocken will be saved in local server and user will be redirected to dashboard page. If response is false the "Invalid credentials message will be displayed"

# api.service.ts (Custom Injectable service)
This service will accept data and do Http api call and retun the data. This service will support GET, POST, PUT api calls. RESTFUL api is consumed in this service.

# dahboard.component.ts
-> In this component all the actions for booking a taxi will be handeled. On load this component will do CAR_TYPES(Normal, pink,  etc..) API call.

-> Once user enter all required details like customer name, mobilenumber, location, slect car type, select radius and click on Get near by cabs then GET_NEARBY_CABS api call will be done and list of drivers will be displayed.

-> After selecting a nearby driver form list user will click on assign cab button, then ASSIGN cab APi will be called and Start trip button will be displayed.

-> once user clicked on start trip the START_TRIP api will be called and end trip form and button will be displayed.

-> once the trip completed the user will enter end location details and time taken and click on End trip button. Then END_TRIP api will be called and Booking details like total fare breakup, total distance covered, time taken details will be displayed.

-> user can start new booking by refershing the page.

# app-routing.module.ts
In this module all the routing configuration will be done.

# BackEnd
index.php will contain all the API'S Mentiond above.

# note : Due to time contraint not able to explain the backend technical flow in detail.
# note : Sceenshots are attached in the mail.
