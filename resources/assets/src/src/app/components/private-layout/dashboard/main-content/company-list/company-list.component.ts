import { RoleGuard } from './../../../../../http/guards/role-guard';
import { IBuyDetails } from './../../../../../storage/ibuy-details';
import { DataService } from './../../../../../storage/data.service';
import { CountDownService } from './../../../../../http/shared/count-down.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { IPaginate } from './../../../../../http/models/search.model';
import { PagerService } from './../../../../../http/shared/pager.service';
import { Component, OnInit } from '@angular/core';
import { NameResarvationService } from '../../../../../http/services/name-resarvation.service';
import { INames, INameChange, IComdata } from '../../../../../http/models/recervationdata.model';
import swal from 'sweetalert2';
import { Router } from '@angular/router';
import { AuthService } from 'src/app/http/shared/auth.service';
import { NameChangeService } from 'src/app/http/services/name-change.service';
import { HelperService } from 'src/app/http/shared/helper.service';
import { Item } from 'src/app/http/models/payment';
import { environment } from '../../../../../../environments/environment';
import { GeneralService } from 'src/app/http/services/general.service';

@Component({
  selector: 'app-company-list',
  templateUrl: './company-list.component.html',
  styleUrls: ['./company-list.component.scss']
})
export class CompanyListComponent implements OnInit {

  public comdata: Array<IComdata> = new Array<IComdata>();
  public pages: IPaginate;
  public current_page = 1;
  public address: string;
  pager: any = {};
  pagedItems: any[];

  now: any;
  enableDate: any;
  dates: any;
  is_admin = '';
  is_other_user = '';
  overseasStrikeOffStatus  = [];
  offshoreStrikeOffStatus = [];

  // Validation With Permission Array

  showIncompleteActions = environment.showIncompleteActions;
  phase3 = environment.phase3;

  constructor(
    private route: Router,
    public rec: NameResarvationService,
    private pagerService: PagerService,
    private spinner: NgxSpinnerService,
    public cd: CountDownService,
    private dataservice: DataService,
    private iBy: IBuyDetails,
    public gard: RoleGuard,
    private auth: AuthService,
    private details: NameChangeService,
    private helper: HelperService,
    private general: GeneralService
  ) { }

  ngOnInit() {
    this.getReceivedName();
  }

  getReceivedName(event: string = '') {
    this.spinner.show();
    this.rec.getReceivedData(this.current_page, event)
      .subscribe(
        req => {
          if (req !== undefined || req.length !== 0) {
            this.comdata = req['data'];
            this.pages = req['meta'];
            this.setPage(this.current_page);
            this.spinner.hide();
            this.is_admin = req['is_admin'];
            this.is_other_user = req['is_other_user'];
           // alert(req['is_admin']);
          }
        }
      );
  }


  selectPage(page: number) {
    this.current_page = page;
    this.getReceivedName();
  }

  setPage(page: number) {
    this.pager = this.pagerService.getPager(this.pages.total, page, this.pages.per_page);
    this.pagedItems = this.comdata.slice(this.pager.startIndex, this.pager.endIndex + 1);
  }

  setTimer(data: string) {
    return this.cd.countDown(data);
  }

  convertAndAdd(date: string) {
    // tslint:disable-next-line:prefer-const
    let dt = new Date(date);
    return dt.setDate(dt.getDate() + 90);
  }


  enableChanegNameButton(date: string, resolution_dates: string) {
    // tslint:disable-next-line:prefer-const
    let dt = new Date(date);
    this.now = new Date();
    this.enableDate = dt.setDate(dt.getDate() + Number(resolution_dates));
    this.dates = new Date(this.enableDate).getDate() - this.now.getDate();
    return new Date(this.enableDate) <= this.now;
  }

  checkDifferentialDays(date: string) {
    return Math.ceil(Math.abs(new Date(date).getTime() - new Date().getTime()) / (1000 * 3600 * 24)) >= 60;
  }

  ngBind(event: string): void {
    this.getReceivedName(event);
  }

  ngRenewal(companyId: string) {
    const item: Array<Item> = [{
      fee_type: 'PAYMENT_NAME_RESERVATION',
      description: 'For approval of a name of a company (Name Renewal)',
      quantity: 1,
    }];

    swal({
      title: 'Are you sure?',
      text: 'You wont be able to renewel this!',
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes, Continue'
    }).then((result) => {
      if (result.value) {
        this.iBy.setItem(item);
        this.iBy.setModuleType('MODULE_NAME_RENEWAL');
        this.iBy.setModuleId(companyId);
        this.iBy.setDescription('Name Renewel');
        this.iBy.setExtraPayment('NAME_RENEWEL');
        this.route.navigate(['reservation/payment']);
      }
    });
  }

  getChangeName(companyId: string, type_id: string) {
    this.dataservice.push(companyId);
    this.dataservice.push(type_id);
    this.route.navigate(['/dashboard/advance/name/reservation']);
  }

  redirectOtherChanges(companyId) {
    this.route.navigate(['/dashboard/home/company/card/' + companyId ]);
  }

  strikeOff(companyId) {
    this.route.navigate(['dashboard/StrikeOff/' + companyId]);
  }

  overseasStrikeOff(companyId) {
    this.route.navigate(['dashboard/OverseasStrikeOff/' + companyId]);
  }

  // getChangeAddress(companyId: string) {
  //   localStorage.setItem('companyId', JSON.stringify(companyId));
  //   localStorage.setItem('status', JSON.stringify('new'));
  //   this.route.navigate(['/dashboard/companyaddresschange']);
  // }

  ngProceed(Id: string, newId: string, newName: string, OldName: string, resubmit: boolean, resolution_date: string) {
    this.dataservice.push(Id);
    this.dataservice.push(newId);
    this.dataservice.push(newName);
    this.dataservice.push(OldName);
    this.dataservice.push(resubmit);
    this.dataservice.push(resolution_date);
  //  alert(resolution_date);
   this.route.navigate(['name/change']);
   return true;

    const data = {
    company_id: newId,
    };
   this.spinner.show();
    this.general.isSetResolutionDate(data)
      .subscribe(
        req => {
           if (req['status']) {
            this.route.navigate(['name/change']);
            this.spinner.hide();
           }else {
           alert('Please set resolution date before proceeding name change by clicking above "Select Signatory" button');
           this.dataservice.cleanData();
            this.spinner.hide();
           }
        }
      );


  }

  ngProceedForForeign(changeId = null, companyId= null ){
    // tslint:disable-next-line:radix
    changeId = changeId ? parseInt(changeId) : null;
      // tslint:disable-next-line:radix
    companyId = companyId ? parseInt(companyId) : null;
    if (!(changeId || companyId) ){
      this.route.navigate(['/dashboard/home']);
    }

    this.route.navigate(['dashboard/notice-of-name-change-of-overseas-company/' + companyId + '/' + changeId]);


  }

  goToAddCompaniesAsAdmin() {
    this.route.navigate(['/dashboard/join-as-admin-with-other-companies']);
  }

  goToAddCompaniesAsOtherUser() {
    this.route.navigate(['/dashboard/join-as-other-user-with-other-companies']);
  }


}
