import { CalculationService } from './../../../../../http/shared/calculation.service';
import { PagerService } from './../../../../../http/shared/pager.service';
import { DataService } from '../../../../../storage/data.service';
import { GeneralService } from '../../../../../http/services/general.service';
import { AuthService } from '../../../../../http/shared/auth.service';
import { AuthenticationService } from '../../../../../http/services/authentication.service';
import { NameResarvationService } from '../../../../../http/services/name-resarvation.service';
import { ISearch, ICompanyType, IHasMeny } from '../../../../../http/models/search.model';
import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { ToastrService } from 'ngx-toastr';
import { NgxSpinnerService } from 'ngx-spinner';
import { FormControl, Validators, FormGroup, FormBuilder } from '@angular/forms';

@Component({
  selector: 'app-advance-search-bar',
  templateUrl: './advance-search-bar.component.html',
  styleUrls: ['./advance-search-bar.component.scss']
})
export class AdvanceSearchBarComponent implements OnInit {
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
  public postfixName: string;
  public comType: number;
  public cRriteria = 1;
  startFrom: any;
  public companyTypeId: number;

  public cardValue: number;

  pager: any = {};
  pagedItems: any[];

  private oldCompanyNumber: string = '';
  public oldCompanytype: number = 0;

  // criterias
  criterias: Array<any> = [
    { label: 'Begins with', value: '2' },
    { label: 'Contains', value: '1' }
  ];

  public selected = this.criterias[0].value; // default selected 1 element for a criteria

  constructor(
    private router: Router,
    private formBuilder: FormBuilder,
    public Authentication: AuthenticationService,
    public Auth: AuthService,
    private Resarvation: NameResarvationService,
    public data: DataService,
    private general: GeneralService,
    private snotifyService: ToastrService,
    private pagerService: PagerService,
    private spinner: NgxSpinnerService,
    public calculation: CalculationService) {

  }

  ngOnInit() {
    if (this.data.getFormData[0] !== undefined && this.data.getFormData[1] !== undefined) {
      this.oldCompanyNumber = this.data.getFormData[0];
      this.oldCompanytype = this.data.getFormData[1];
    }

    this.data.cleanData();
    this.postfixName = '';
    this.formGroup = this.formBuilder.group({
      companyType: new FormControl({value: this.oldCompanytype, disabled: this.oldCompanytype !== 0}),
      postfix: new FormControl(''),
      criteria: new FormControl(this.cRriteria, [
        Validators.required
      ]),
      search: new FormControl(this.search.searchtext, Validators.required)
    });

    this.getCompanyType();

    this.formGroup.controls['criteria'].setValue(this.selected, { onlySelf: true });
  }

  get companyType() { return this.formGroup.get('companyType'); }

  get postfix() { return this.formGroup.get('postfix'); }

  get criteria() { return this.formGroup.get('criteria'); }

  get searchtext() { return this.formGroup.get('search'); }

  changeCriteria(event: number) {
    this.cRriteria = event;
  }

  ckSearch(): void {

    const newstr = this.searchtext.value.replace(this.postfix.value, '');

    if (this.postfix.value === '0') {
      this.snotifyService.warning('Please select the type of company', 'warning');
      return;
    }

    if (!this.searchtext.value) { this.seResults = undefined; this.pages = undefined; return; }

    this.startFrom = new Date().getTime();
    this.spinner.show();
    const searchData: ISearch = {
      criteria: this.criteria.value,
      searchtext: newstr,
      companyType: this.companyType.value,
      // tslint:disable-next-line:max-line-length
      token: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjBiZjNjZWNjNjQ4MWY3ZWYwZWFlNGZmYzJhMjZjMDMwMWFhYTJjY2U2NWVlMmRiZjdkMjg1NjBjYjZlMTM1ODIyYTQ5MGZiMTdjNDhkYmZiIn0'
    };

    this.Resarvation.getSearchResult(searchData, this.current_page)
      .subscribe(
        req => {
          this.availableData = req['availableData'];
          this.notHasData = req['notHasData'];
          this.searchName = newstr;
          this.postfixName = this.postfix.value;
          this.comType = this.companyType.value;
          this.setPage(this.current_page);
          this.spinner.hide();
          this.startFrom = (new Date().getTime() - this.startFrom) / 1000.0;
          this.getCardValue();
        },
        error => {
          this.spinner.hide();
          this.snotifyService.error('request wrong', 'error');
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

  getCompanyType() {
    this.spinner.show();
    this.general.getCompanyType().subscribe(
      req => {
        this.companyTypes = req;
        this.companyTypeId = this.oldCompanytype !== 0 ? this.oldCompanytype : req[0]['id'];
        this.formGroup.controls['companyType'].setValue(this.oldCompanytype, { onlySelf: true });
        this.getSubCatogory();
      }
    );
  }

  getSubCatogory(event: any = null) {
    if (event !== null) {
      this.companyTypeId = event.target.value;
    }
    this.general.getComSubData(this.companyTypeId).subscribe(
      req => {
        if (req === undefined || req.length === 0) {
          this.postfix.reset();
          this.subCatogorys = undefined;
          this.spinner.hide();
        } else {
          this.subCatogorys = req;
          this.formGroup.controls['postfix'].setValue('0', { onlySelf: true });
          this.spinner.hide();
        }
      }
    );
  }

  Clean() {
    this.availableData = undefined;
    this.notHasData = undefined;
    this.pages = undefined;
    this.ngOnInit();
  }

  onResavation() {

    this.Resarvation.isCheckPostfix(this.companyType.value)
      .subscribe(
        req => {
          if (req !== true) {
            if (!this.postfixName) {
              this.ckSearch();
              this.snotifyService.warning('Please select the type of company', 'warning');
              return;
            }
          }
        }
      );

    const storage = {
      name: this.searchName,
      postfix: this.postfixName,
      comType: this.comType,
      oldnumber: this.oldCompanyNumber
    };

    this.data.setLocaldata('ResName', storage);
    this.router.navigate(['/reservation']);
  }

  getCardValue() {
    this.calculation.getValue('PAYMENT_NAME_RESERVATION');

   // this.cardValue = this.calculation.getTotalAmount;


    var forReservation = parseFloat( this.calculation.getValue('PAYMENT_NAME_RESERVATION').toString());
    forReservation = forReservation + ( forReservation *  parseFloat(this.calculation.getTaxPer.toString()) ) / 100;

    this.cardValue = forReservation  + (forReservation * parseFloat( this.calculation.getConvenienceFeePer.toString())) / 100;
  }


}

