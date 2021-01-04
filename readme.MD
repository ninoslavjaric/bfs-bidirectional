# INITIALIZATION

## Requirements
- [docker](https://docs.docker.com/engine/install/ubuntu/)
- [docker-compose](https://docs.docker.com/compose/install/)

File `.env` is gitignored. It contains required parameters on initialization
Structure:
```dotenv
DB_NAME=databasename
DB_USER=dbusername
DB_PASSWORD=dbuserpassword
APP_ADMIN=appusername
APP_ADMINPASS=adminapppassword
MYSQL_ROOT_PASSWORD=rootpassword
```

Initialization is being run by `docker-compose up -d`
Initialization steps:
 - generating database
 - generating db user being used by the app
 - generating config file for data access layer `ubuntu/var.www.html/config/database.local.php`
 - injecting db structure from `ubuntu/setup/db.sql` dump
 - generating admin user
 
 After initialization execute `composer install` in ubuntu/var.www.html
 
 # Endpoints
 
 Accesing clasified endpoints is feasible by passing request http header 'htec-token: <token>'
 
 ## Index
    GET /
        returns hello world message
 ## Airport
    POST /airport/import
    Key request headers
    - Content-Type: plain/text
    Request body: content structure from provided test case
    
    Response on success:
    {
        "message": "Import successful",
        "success": true
    }
    
 ## Auth
##### Requesting token
    POST /auth
    Key request headers
    - Content-Type: application/json
    Request body: 
    - example
    {
        "username": "petar.petrovic",
        "password": "true"
    }
    
    Response on success:
    {
        "message": "Token generated",
        "success": true,
        "data": {
            "token": "c8fa717192411f27aaeb2da65dac7193840218de"
        }
    }
##### Registering
    POST /auth/register
    Key request headers
    - Content-Type: application/json
    Request body: 
    - example
    {
        "firstName": "Petar",
        "lastName": "Petrovic",
        "username": "petar.petrovic",
        "password": "true"
    }
    Response on success:
    
    {
        "message": "Registration successful",
        "success": true
    }
 ## City
##### Getting cities list
    GET /city
    Response on success:
    
    {
        message: "Cities found",
        success: true,
        data: [
            {
                id: "8536",
                countryId: "367",
                name: "Herat",
                countryName: "Afghanistan",
                comments: [ ]
            },
            .........
        ]
    }

##### Searching cities
    GET /city/search/<string>
    Response on success:
    
    {
        message: "Cities found",
        success: true,
        data: [
            {
                id: "6972",
                countryId: "252",
                name: "Munich",
                countryName: "Germany",
                comments: [ ]
            }
        ]
    }

##### Creating
    POST /city/create
    Key request headers
    - Content-Type: application/json
    Request body: 
    - example
    {
        "name":"Nigdafsddfsegradlaa",
        "country":"Zemlja",
        "comment":"Ovo je nidje veze nesto"
    }
    
    Response on success:
    {
        "message": "City created",
        "success": true,
        "data": {
            "id": "13283",
            "country_id": "475",
            "name": "Nigdafsddfsegradlaa",
            "country_name": "Zemlja",
            "comments": [
                "Ovo je nidje veze nesto"
            ]
        }
    }
 ## Comment
 ##### Creating comment
    POST /comment/create
    Key request headers
    - Content-Type: application/json
    Request body: 
    - example
    {
        "text": "This is wow",
        "cityId": 6972
    }
    
    Response on success:
    {
        "message": "Comment created ",
        "success": true,
        "data": {
            "id": "8",
            "text": "This is wow",
            "city_id": "6972",
            "user_id": "1"
        }
    }
 ##### Updating comment
    POST /comment/update
    Key request headers
    - Content-Type: application/json
    Request body: 
    - example
    {
        "id": 8,
        "text": "This is waw",
        "cityId": 6972
    }
    
    Response on success:
    {
        "message": "Comment updated ",
        "success": true,
        "data": {
            "id": "8",
            "text": "This is waw",
            "city_id": "6972",
            "user_id": "1"
        }
    }
 ##### Deleting comment
    DELETE /comment/delete
    Key request headers
    - Content-Type: application/json
    Request body: 
    - example
    {
        "id":1
    }
    
    Response on success:
    {
        "message": "Comment deleted.",
        "success": true
    }
 ## Route
 ##### Import
    POST /route/import
    Key request headers
    - Content-Type: plain/text
    Request body: content structure from provided test case
    
    Response on success:
    {
        "message": "Import successful",
        "success": true
    }
 
 ##### Travel
    GET /route/travel/<sourceCityId>/<destinationCityId>
    Response on success:
    
    {
      message: "Optimal flight found",
      success: true,
      data: {
        path: [
          {
            id: "98546",
            airlineId: "132",
            sourceAirportId: "5794",
            destinationAirportId: "1646",
            codeshare: "",
            stops: "0",
            equipment: "AT7",
            price: "21.03",
            sourceAirport: {
              id: "5794",
              name: "Banja Luka International Airport",
              cityId: "10844",
              iata: "BNX",
              icao: "LQBK",
              latitude: "44.9414",
              longitude: "17.2975",
              altitude: "400",
              timezone: "1",
              dst: "E",
              dbTimezone: "Europe/Sarajevo",
              type: "airport",
              source: "OurAirports"
            },
            destinationAirport: {
              id: "1646",
              name: "Sarajevo International Airport",
              cityId: "8178",
              iata: "SJJ",
              icao: "LQSA",
              latitude: "43.8246",
              longitude: "18.3315",
              altitude: "1708",
              timezone: "1",
              dst: "E",
              dbTimezone: "Europe/Sarajevo",
              type: "airport",
              source: "OurAirports"
            },
            sourceCity: {
              id: "10844",
              countryId: "330",
              name: "Banja Luka",
              countryName: "Bosnia and Herzegovina",
              comments: [ ]
            },
            destinationCity: {
              id: "8178",
              countryId: "330",
              name: "Sarajevo",
              countryName: "Bosnia and Herzegovina",
              comments: [ ]
            },
            distance: 148.90402073682515
          },
          {
            id: "98550",
            airlineId: "132",
            sourceAirportId: "1646",
            destinationAirportId: "1739",
            codeshare: "",
            stops: "0",
            equipment: "AT7",
            price: "25.08",
            sourceAirport: {
              id: "1646",
              name: "Sarajevo International Airport",
              cityId: "8178",
              iata: "SJJ",
              icao: "LQSA",
              latitude: "43.8246",
              longitude: "18.3315",
              altitude: "1708",
              timezone: "1",
              dst: "E",
              dbTimezone: "Europe/Sarajevo",
              type: "airport",
              source: "OurAirports"
            },
            destinationAirport: {
              id: "1739",
              name: "Belgrade Nikola Tesla Airport",
              cityId: "8250",
              iata: "BEG",
              icao: "LYBE",
              latitude: "44.8184",
              longitude: "20.3091",
              altitude: "335",
              timezone: "1",
              dst: "E",
              dbTimezone: "Europe/Belgrade",
              type: "airport",
              source: "OurAirports"
            },
            sourceCity: {
              id: "8178",
              countryId: "330",
              name: "Sarajevo",
              countryName: "Bosnia and Herzegovina",
              comments: [ ]
            },
            destinationCity: {
              id: "8250",
              countryId: "337",
              name: "Belgrade",
              countryName: "Serbia",
              comments: [ ]
            },
            distance: 192.2452982136684
          },
          {
            id: "126457",
            airlineId: "5461",
            sourceAirportId: "1739",
            destinationAirportId: "664",
            codeshare: "",
            stops: "0",
            equipment: "320",
            price: "22.08",
            sourceAirport: {
              id: "1739",
              name: "Belgrade Nikola Tesla Airport",
              cityId: "8250",
              iata: "BEG",
              icao: "LYBE",
              latitude: "44.8184",
              longitude: "20.3091",
              altitude: "335",
              timezone: "1",
              dst: "E",
              dbTimezone: "Europe/Belgrade",
              type: "airport",
              source: "OurAirports"
            },
            destinationAirport: {
              id: "664",
              name: "Sandefjord Airport, Torp",
              cityId: "7274",
              iata: "TRF",
              icao: "ENTO",
              latitude: "59.1867",
              longitude: "10.2586",
              altitude: "286",
              timezone: "1",
              dst: "E",
              dbTimezone: "Europe/Oslo",
              type: "airport",
              source: "OurAirports"
            },
            sourceCity: {
              id: "8250",
              countryId: "337",
              name: "Belgrade",
              countryName: "Serbia",
              comments: [ ]
            },
            destinationCity: {
              id: "7274",
              countryId: "265",
              name: "Sandefjord",
              countryName: "Norway",
              comments: [ ]
            },
            distance: 1735.0651002717855
          },
          {
            id: "126813",
            airlineId: "5461",
            sourceAirportId: "664",
            destinationAirportId: "1657",
            codeshare: "",
            stops: "0",
            equipment: "320",
            price: "24.84",
            sourceAirport: {
              id: "664",
              name: "Sandefjord Airport, Torp",
              cityId: "7274",
              iata: "TRF",
              icao: "ENTO",
              latitude: "59.1867",
              longitude: "10.2586",
              altitude: "286",
              timezone: "1",
              dst: "E",
              dbTimezone: "Europe/Oslo",
              type: "airport",
              source: "OurAirports"
            },
            destinationAirport: {
              id: "1657",
              name: "Henri Coandă International Airport",
              cityId: "8182",
              iata: "OTP",
              icao: "LROP",
              latitude: "44.5711",
              longitude: "26.085",
              altitude: "314",
              timezone: "2",
              dst: "E",
              dbTimezone: "Europe/Bucharest",
              type: "airport",
              source: "OurAirports"
            },
            sourceCity: {
              id: "7274",
              countryId: "265",
              name: "Sandefjord",
              countryName: "Norway",
              comments: [ ]
            },
            destinationCity: {
              id: "8182",
              countryId: "331",
              name: "Bucharest",
              countryName: "Romania",
              comments: [ ]
            },
            distance: 1943.9895533658612
          }
        ],
        distance: "4020.202407836914",
        price: "93.03000068664551"
      }
    }
