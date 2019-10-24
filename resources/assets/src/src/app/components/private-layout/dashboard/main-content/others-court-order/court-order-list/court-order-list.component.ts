import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { APIConnection } from '../../../../../../http/services/connections/APIConnection';
import { NgxSpinnerService } from 'ngx-spinner';
import { OtherCourtService } from '../service/other-court.service';

@Component({
  selector: 'app-court-order-list',
  templateUrl: './court-order-list.component.html',
  styleUrls: ['./court-order-list.component.scss']
})
export class CourtOrderListComponent implements OnInit {

  url: APIConnection = new APIConnection();

  namePart = '';
  registration_no = '';
  request_id = '';

  totalResultPages: number = null;
  currentPage: 1;
  companyList = { list: [] };

  constructor(
    private router: Router,
    private spinner: NgxSpinnerService,
    private otherCourtService: OtherCourtService,
  ) { }

  ngOnInit() {
    this.getCourtOrderList();
  }

  getCourtOrderList(page = 1) {

    page = (isNaN(page)) ? 0 : (page - 1);
    page = (page <= 0) ? 0 : page;


    const data = {
      namePart: this.namePart,
      registration_no: this.registration_no,
      request_id: this.request_id,
      page: page
    };
    this.spinner.show();

    //  if (!(this.namePart || this.registration_no || this.request_id) ){
    //    this.spinner.hide();
    //    return false;
    //  }

    // load Company data from the server
    this.otherCourtService.getCourtOrderList(data)
      .subscribe(
        req => {
          let corrs = req['courtOrderList'];
          this.companyList.list = [];
          for (let i in corrs) {

            let c = {
              request_id: corrs[i]['request_id'],
              company_id: corrs[i]['company_id'],
              company_name: corrs[i]['company_name'],
              status: corrs[i]['status'],
              date: corrs[i]['date'],
              // comment: corrs[i]['comment'],
              // reg_no: corrs[i]['reg_no']
            };

            this.companyList.list.push(c);

          }

          // tslint:disable-next-line:radix
          this.totalResultPages = parseInt(req['total_pages']);
          this.currentPage = req['current_page'];

          this.spinner.hide();


        }
      );
  }


  goToExist(companyId, requestId) {
    this.router.navigate(['dashboard/othersCourtOrderList/' + companyId + '/' + requestId]);
  }

  createNew() {
    this.router.navigate(['/dashboard/othersCourtOrder-search']);
  }

  deleteList(companyId, requestId) {
    const data = {
      requestId: requestId,
      companyId: companyId,
    };
    this.spinner.show();
    this.otherCourtService.removeList(data)
      .subscribe(
        req => {
          // this.getCorrespondenceList();
          this.spinner.hide();
        }
      );
  }
}
