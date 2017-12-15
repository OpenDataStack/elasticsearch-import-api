// NOTE: For use with elastic search vs code plugin

// Test creation of index template

PUT _template/dkan-999
{
  "index_patterns": ["dkan-999-*"],
  "settings": {
    "number_of_shards": 1
  },
  "mappings": {
      "contract": {
        "_meta": { 
            "column_map": {
                "Initiation Type": "initiationType",
                "ocid": "contractId"
            }
        },
        "properties": {
          "ocid": {
            "type": "keyword"
          },
          "date": {
            "type": "date",
            "ignore_malformed": true
          },
          "tag": {
            "type": "keyword"
          },
          "initiationType": {
            "type": "keyword"
          },
          "publishedDate": {
            "type": "date",
            "ignore_malformed": true
          },
          "id": {
            "type": "keyword"
          },
          "language": {
            "type": "keyword"
          },
          "uri": {
            "type": "keyword"
          },
          "procurement_type": {
            "type": "keyword"
          },
          "buyer_name": {
            "type": "text"
          },
          "buyer_identifier_scheme": {
            "type": "keyword"
          },
          "buyer_identifier_id": {
            "type": "keyword"
          },
          "buyer_address_countryName": {
            "type": "keyword"
          },
          "buyer_address_region": {
            "type": "keyword"
          },
          "buyer_address_locality": {
            "type": "text"
          },
          "buyer_address_streetAddress": {
            "type": "text"
          },
          "tender_id": {
            "type": "keyword"
          },
          "tender_status": {
            "type": "keyword"
          },
          "tender_description": {
            "type": "text"
          },
          "tender_title": {
            "type": "text"
          },
          "tender_value_currency": {
            "type": "keyword"
          },
          "tender_value_amount": {
            "type": "float",
            "ignore_malformed": true
          },
          "tender_items_0_id": {
            "type": "integer",
            "ignore_malformed": true
          },
          "tender_items_0_classification_id": {
            "type": "integer",
            "ignore_malformed": true
          },
          "tender_items_0_classification_description": {
            "type": "text"
          },
          "tender_items_0_classification_scheme": {
            "type": "keyword"
          },
          "tender_items_0_classification_uri": {
            "type": "keyword"
          },
          "tender_tenderPeriod_startDate": {
            "type": "keyword"
          },
          "tender_tenderPeriod_endDate": {
            "type": "keyword"
          }
        }
      }
  }
}


PUT _template/dkan-111
{
  "index_patterns": ["dkan-999-*"],
  "settings": {
    "number_of_shards": 1
  },
  "mappings": {
      "contract": {
        "properties": {
          "ocid": {
            "type": "keyword"
          },
          "date": {
            "type": "date",
            "ignore_malformed": true
          }
        }
      }
  }
}

PUT _template/dkan-222
{
  "index_patterns": ["dkan-777-*"],
  "settings": {
    "number_of_shards": 1
  },
  "mappings": {
      "row": {
        "properties": {
          "ocid": {
            "type": "keyword"
          },
          "date": {
            "type": "date",
            "ignore_malformed": true
          }
        }
      }
  }
}

DELETE _template/dkan-222

GET /_template

DELETE _template/dkan-999

// Test creation of index that does not match pattern

PUT testindex
{
    "settings" : {
        "index" : {
            "number_of_shards" : 3, 
            "number_of_replicas" : 2 
        }
    }
}

PUT testindex

GET testindex

DELETE testindex

GET /_cat/indices


// Test creation of index that DOES match pattern

PUT dkan-888-abc

GET dkan-888-abc

DELETE dkan-888-abc

GET /_cat/indices

// Symfony testing

GET dkan-999-abc

DELETE dkan-999-abc


GET /dkan-999-abc/_search?size=10&q=*:*