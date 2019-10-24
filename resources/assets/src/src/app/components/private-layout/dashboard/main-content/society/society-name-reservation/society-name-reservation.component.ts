import { CalculationService } from './../../../../../../http/shared/calculation.service';
import { PagerService } from './../../../../../../http/shared/pager.service';
import { GeneralService } from './../../../../../../http/services/general.service';
import { DataService } from './../../../../../../storage/data.service';
import { NameResarvationService } from './../../../../../../http/services/name-resarvation.service';
import { AuthService } from './../../../../../../http/shared/auth.service';
import { AuthenticationService } from './../../../../../../http/services/authentication.service';
import { ICompanyType, ISearch, IHasMeny } from './../../../../../../http/models/search.model';
import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { ToastrService } from 'ngx-toastr';
import { NgxSpinnerService } from 'ngx-spinner';
import { FormControl, Validators, FormGroup, FormBuilder } from '@angular/forms';


@Component({
  selector: 'app-society-name-reservation',
  templateUrl: './society-name-reservation.component.html',
  styleUrls: ['./society-name-reservation.component.scss']
})
export class SocietyNameReservationComponent implements OnInit {


  public formGroup: FormGroup;
  private search: ISearch = { criteria: 1, searchtext: '', companyType: 0, token: '' };
  public subCatogorys: Array<any> = [];

  companyTypes: ICompanyType;

  public current_page = 1;

  public availableData: IHasMeny;
  public notHasData: IHasMeny;
  public seResults: Array<any>;
  public dataLink: Array<any>;


  public pages: Array<number>;

  public endPage: number;
  public startPage: number;

  public available: boolean;
  public searchName: string;
  public comType: number;


  startFrom: any;
  public companyTypeId: number;

  pager: any = {};
  pagedItems: any[];

  constructor(
    private router: Router,
    private formBuilder: FormBuilder,
    public Authentication: AuthenticationService,
    public Auth: AuthService,
    private Resarvation: NameResarvationService,
    public data: DataService,
    private snotifyService: ToastrService,
    private pagerService: PagerService,
    private spinner: NgxSpinnerService,
    public Calculation: CalculationService) {

  }

  ngOnInit() {
    this.formGroup = this.formBuilder.group({
      search: new FormControl(this.search.searchtext, Validators.required)
    });
  }

  get companyType() { return this.formGroup.get('companyType'); }

  get searchtext() { return this.formGroup.get('search'); }

  ckSearch(): void {

    if (!this.searchtext.value) { this.seResults = undefined; this.pages = undefined; return; }

    this.startFrom = new Date().getTime();
    this.spinner.show();
    const searchData: ISearch = {
      searchtext: this.searchtext.value,
      companyType: 55555,
      // tslint:disable-next-line:max-line-length
      token: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjBiZjNjZWNjNjQ4MWY3ZWYwZWFlNGZmYzJhMjZjMDMwMWFhYTJjY2U2NWVlMmRiZjdkMjg1NjBjYjZlMTM1ODIyYTQ5MGZiMTdjNDhkYmZiIn0'
    };

    this.Resarvation.getSearchResultSociety(searchData, this.current_page)
      .subscribe(
        req => {
          this.availableData = req['availableData'];
          this.notHasData = req['notHasData'];
          this.searchName = this.searchtext.value;
          this.comType = 55555;
          this.setPage(this.current_page);
          this.startFrom = (new Date().getTime() - this.startFrom) / 1000.0;
          this.spinner.hide();
        },
        error => {
          this.spinner.hide();
          this.snotifyService.error('oops something went wrong', 'error');
        }
      );
  }
  selectPage(page: number) {
    this.current_page = page;
    this.ckSearch();
  }

  setPage(page: number) {
    this.pager = this.pagerService.getPager(this.availableData.meta.total, page, this.availableData.meta.per_page);
    this.pagedItems = this.availableData.data.slice(this.pager.startIndex, this.pager.endIndex + 1);
  }

  ckPageZero(): void {
    this.current_page = 0;
  }

  Clean() {
    this.availableData = undefined;
    this.notHasData = undefined;
    this.pages = undefined;
    this.ngOnInit();
  }

  onResavation() {
    this.data.storage  = {
      name: this.searchName,
      needApproval: this.needMinisterApproval()
    };
    this.router.navigate(['namewithagreesociety']);
  }

  needMinisterApproval() {
    for (let data of this.notHasData.data) {
      if (data.message === 'SOCIETY and LIMITED both words are not used so need approval letter from minister.') {
        return true;
      }
    }
    return false;
  }



}

