import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { NgxSpinnerService } from 'ngx-spinner';

import { TenderService } from '../services/tender.service';
import { ICloseTenderMember, ICloseTenderMembers, ICloseTenderItem, ICloseTenderItems, ITender, ItenderListItems, ItenderListItem, ItenderPublicationLists} from '../models/tender.model';
import { HttpHeaders } from '@angular/common/http';
import { HttpClient } from '@angular/common/http';
import { APITenderConnection } from '../services/connections/APITenderConnection';

@Component({
  selector: 'app-list-user-tender',
  templateUrl: './list-user-tender.component.html',
  styleUrls: ['./list-user-tender.component.scss']
})
export class ListUserTenderComponent implements OnInit {

  url: APITenderConnection = new APITenderConnection();
  listItems: ItenderPublicationLists = { list: [] };

  // messages
  emptyListMessage = '';

  loginUser: string = localStorage.getItem('currentUser');

  constructor( private route: ActivatedRoute,
    private router: Router,
    private spinner: NgxSpinnerService,
    private httpClient: HttpClient,
    private tenderService: TenderService) {
      this.getUserTenders();

  }

  ngOnInit() {
  }

  getUserTenders() {

    const data = {
      loginUser : this.loginUser
    };
    this.spinner.show();

    // load Company data from the server
    this.tenderService.userTendersGet(data)
      .subscribe(
        req => {

            if (req['tenderCount']) {
              this.listItems = req['tenderList'];
              // tslint:disable-next-line:prefer-const
              let tendersList = req['tenderList'];

              // tslint:disable-next-line:prefer-const
             /* for ( let i in tendersList ) {

                // tslint:disable-next-line:prefer-const
                let listItem: ItenderListItem = {
                    type: tendersList[i]['type'],
                    number: tendersList[i]['number'],
                    name : tendersList[i]['name'],
                    description: tendersList[i]['descriptin'],
                  //  from: tendersList[i]['from'],
                  //  to: tendersList[i]['to'],
                    id: tendersList[i]['id'],
                    publicationId : tendersList[i]['publication_id']

                };
                this.listItems.items.push(listItem);

              }*/
              this.emptyListMessage = '';

            } else {
              this.emptyListMessage = 'No Tenders published yet';

            }
            this.spinner.hide();

        }
      );
  }

  openList(publicationIdRow) {
    this.listItems.list[publicationIdRow].openList = true;
  }

  closeList(publicationIdRow) {
    this.listItems.list[publicationIdRow].openList = false;
    this.getUserTenders();
   // console.log(this.listItems.list);
  }

  goToTender(tenderId) {

    this.router.navigate(['/dashboard/tenders/edit-tender/' + tenderId ]);

  }
  goToTenderAdd() {
    this.router.navigate(['/dashboard/tenders/create-tender/']);
  }


}
