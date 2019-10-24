import { Component, OnInit } from '@angular/core';
import { TenderService } from '../../services/tender.service';
import { ICloseTenderMember, ICloseTenderMembers, ICloseTenderItem, ICloseTenderItems, ITender, ItenderListItems, ItenderListItem} from '../../models/tender.model';
import { HttpHeaders } from '@angular/common/http';
import { HttpClient } from '@angular/common/http';
import { APITenderConnection } from '../../services/connections/APITenderConnection';
import { ActivatedRoute, Router } from '@angular/router';
import { NgxSpinnerService } from 'ngx-spinner';
import { ChangeDetectionStrategy } from '@angular/core';

@Component({
  selector: 'app-list-tenders',
  templateUrl: './list-tenders.component.html',
  styleUrls: ['./list-tenders.component.scss']
})
export class ListTendersComponent implements OnInit {

  url: APITenderConnection = new APITenderConnection();

  ref_no = '';
  selectedPublisher = null;
  publisherDivision = '';
  tenderNamePart = '';
  tenderNo = '';

  listItems: ItenderListItems = { items: [] };
  emptyListMessage = '';
  closeTenderItem: ItenderListItem = {'type': 'close' , number: '', name: '', description: '', id: null, publicationId: null, appliedCount: null, publisherName: '', publishedDate : '' };
  emptyCloseTenderMessage = '';

  totalResultPages: number = null;
  currentPage: 1;


  publisherDropdownOptions = [];
  selectedPublisherOpt: Array<{id: number ; name: string}> = null;
  config = {
    displayKey: 'name', // if objects array passed which key to be displayed defaults to description
    search: true, // true/false for the search functionlity defaults to false,
    height: 'auto', // height of the list so that if there are more no of items it can show a scroll defaults to auto. With auto height scroll will never appear
    placeholder: 'Select the publisher', // text to be displayed when no item is selected defaults to Select,
    customComparator: () => {}, // a custom function using which user wants to sort the items. default is undefined and Array.sort() will be used in that case,
  };

  constructor( private route: ActivatedRoute,
    private router: Router,
    private spinner: NgxSpinnerService,
    private httpClient: HttpClient,
    private tenderService: TenderService) {

  }


  ngOnInit() {
    this.getTenders();
  }

  getTendersWithClose() {

    this.getTenders();
  }

  removeLowerSearch() {
    this.tenderNo = '';
  }
  removeUpperSearch() {
    this.tenderNamePart = '';
    this.selectedPublisher = null;
    this.publisherDivision = '';
  }

  getTenders(page = 1 ) {

    page = ( isNaN(page) ) ? 0 : ( page - 1 ) ;
    page = (page <= 0 ) ? 0 : page;

    this.listItems =  { items: [] };

    const data = {
      ref_no : this.ref_no,
      publisher : this.selectedPublisher,
      tenderNamePart : this.tenderNamePart,
      tenderNo : this.tenderNo,
      publisherDivision : this.publisherDivision,
      page : page
    };
    this.spinner.show();

    // load Company data from the server
    this.tenderService.getTenders(data)
      .subscribe(
        req => {

            if (req['tenderCount']) {

              // tslint:disable-next-line:radix
              this.totalResultPages = parseInt( req['total_pages'] );
              this.currentPage = req['current_page'];


              // tslint:disable-next-line:prefer-const
              let publisherListArr = req['publisherList'];

              this.publisherDropdownOptions = [];
              // tslint:disable-next-line:prefer-const
              let publisherListIds = [];
              // tslint:disable-next-line:prefer-const
              for ( let i in publisherListArr ) {

                if (publisherListIds.indexOf( publisherListArr[i]['id']) < 0 ) { // this fix the publisher duplicauton issue
                  this.publisherDropdownOptions.push(publisherListArr[i]);
                  publisherListIds.push(publisherListArr[i]['id']);
                }
              }

              // tslint:disable-next-line:prefer-const
              let tendersList = req['tenderList'];

              // tslint:disable-next-line:prefer-const
              for ( let i in tendersList ) {

                // tslint:disable-next-line:prefer-const
                let listItem: ItenderListItem = {
                    type: tendersList[i]['type'],
                    number: tendersList[i]['number'],
                    name: tendersList[i]['name'],
                    description: tendersList[i]['descriptin'],
                    ministry : tendersList[i]['ministry'],
                    // from: tendersList[i]['from'],
                   // to: tendersList[i]['to'],
                    id: tendersList[i]['id'],
                    publicationId : tendersList[i]['publication_id'],
                    publisherName : tendersList[i]['publisher_name'],
                    publishedDate : tendersList[i]['published_date']
                };
                this.listItems.items.push(listItem);

              }
              this.emptyListMessage = '';

            } else {
              this.totalResultPages = 0;
              this.emptyListMessage = 'No Tenders published yet';

            }
            this.spinner.hide();

        }
      );
  }


  getCloseTender() {

    const data = {
      ref_no : this.ref_no,
    };
    this.spinner.show();

    // load Company data from the server
    this.tenderService.getCloseTender(data)
      .subscribe(
        req => {

            if (req['status']) {
               this.closeTenderItem.type = 'close';
               this.closeTenderItem.number = req['closeTender']['number'];
               this.closeTenderItem.name = req['closeTender']['name'];
               this.closeTenderItem.description = req['closeTender']['descriptin'];
               this.closeTenderItem.publisherName = req['closeTender']['publisher_name'];
               this.closeTenderItem.id = req['closeTender']['id'];
               this.closeTenderItem.publishedDate = req['closeTender']['published_date'];
              this.emptyCloseTenderMessage = '';
            } else {
              this.emptyCloseTenderMessage = 'No Close Tenders for Your Ref. Code';
              this.closeTenderItem = {'type': 'close' , number: '', name: '', description: '', id: null, publicationId: null, appliedCount: null, publisherName: '', publishedDate : '' };
            }
            this.spinner.hide();
        }
      );
  }

  selectionPublisherChanged(evt) {
  //  console.log( this.selectedPublisherOpt );
  //  console.log( this.selectedPublisherOpt[0].name );
  }

  goToTender(tenderId) {

      this.router.navigate(['/home/tenders/apply/' + tenderId ]);
    //  this.router.navigate(['/home/tenders/resubmit/' + tenderId + '/' + '1111' ]);
    // this.router.navigate(['/home/tenders/awarding/' + tenderId + '/' + 'a0c02989502252329f1a9e38f04510b1' ]);
  }

}
