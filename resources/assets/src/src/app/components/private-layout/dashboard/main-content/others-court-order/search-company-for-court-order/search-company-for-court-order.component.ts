import { Component, OnInit } from '@angular/core';
import { APIConnection } from '../../../../../../http/services/connections/APIConnection';
import { Router, ActivatedRoute } from '@angular/router';
import { NgxSpinnerService } from 'ngx-spinner';
import { OtherCourtService } from '../service/other-court.service';

@Component({
  selector: 'app-search-company-for-court-order',
  templateUrl: './search-company-for-court-order.component.html',
  styleUrls: ['./search-company-for-court-order.component.scss']
})
export class SearchCompanyForCourtOrderComponent implements OnInit {


  url: APIConnection = new APIConnection();

  namePart = '';
  registration_no = '';

  emptyCloseTenderMessage = '';

  totalResultPages: number = null;
  currentPage: 1;
  companyList = [];

  constructor(
    private router: Router,
    private spinner: NgxSpinnerService,
    private otherCourtService: OtherCourtService,
  ) { }

  ngOnInit() {
    // console.log(this.namePart);
    // console.log(this.registration_no);
  }

  getCompanies(page = 1) {

    page = (isNaN(page)) ? 0 : (page - 1);
    page = (page <= 0) ? 0 : page;


    const data = {
      namePart: this.namePart,
      registration_no: this.registration_no,
      page: page
    };
    this.spinner.show();

    if (!(this.namePart || this.registration_no)) {
      this.spinner.hide();
      return false;
    }

    // load Company data from the server
    this.otherCourtService.getCompaniesForCourtOrder(data)
      .subscribe(
        req => {
          let companies = req['companyList'];
          this.companyList = [];
          for (let i in companies) {

            let c = {
              company_id: companies[i]['id'],
              name: companies[i]['name'],
              regNo: companies[i]['registration_no'],
              compStatus: companies[i]['compStatus'],
              request_id: companies[i]['reqId'],

            };

            this.companyList.push(c);

          }

          // tslint:disable-next-line:radix
          this.totalResultPages = parseInt(req['total_pages']);
          this.currentPage = req['current_page'];

          this.spinner.hide();


        }
      );
  }

  goToExist(companyId, requestId) {
    this.router.navigate(['dashboard/othersCourtOrder/' + companyId + '/' + requestId]);
  }

  goToNew(companyId, status) {
    this.router.navigate(['dashboard/othersCourtOrder/' + companyId + '/' + status]);
  }

}
