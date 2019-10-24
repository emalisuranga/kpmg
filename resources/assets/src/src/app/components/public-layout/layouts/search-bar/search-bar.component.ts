import { ISearch, ICompanyType, IHasMeny } from './../../../../http/models/search.model';
import { NgxSpinnerService } from 'ngx-spinner';
import { ToastrService } from 'ngx-toastr';
import { GeneralService } from './../../../../http/services/general.service';
import { DataService } from './../../../../storage/data.service';
import { NameResarvationService } from './../../../../http/services/name-resarvation.service';
import { PagerService } from './../../../../http/shared/pager.service';
import { AuthService } from './../../../../http/shared/auth.service';
import { AuthenticationService } from './../../../../http/services/authentication.service';
import { FormBuilder, Validators, FormControl, FormGroup } from '@angular/forms';
import { Router } from '@angular/router';
import { Component, OnInit } from '@angular/core';

@Component ({
  selector: 'app-search-bar',
  templateUrl: './search-bar.component.html',
  styleUrls: ['./search-bar.component.scss']
})
export class SearchBarComponent implements OnInit {
  public formGroup: FormGroup;
  private search: ISearch = { criteria: 1, searchtext: '', companyType: 0, token: '' };
  public subCatogorys: Array<any> = [];

  public current_page: number;

  public availableData: IHasMeny;
  public notHasData: IHasMeny;
  public seResults: Array<any>;
  public dataLink: Array<any>;
  public pages: Array<number>;
  public loopNumber: Array<number> = new Array(10);
  public meta: Array<any>;
  public available: boolean;
  public searchName: string;
  public postfixName: string;
  public comType: number;
  public cRriteria = 2;
  startFrom: any;

  pager: any = {};
  pagedItems: any[];

  // criterias
  criterias: Array<any> = [
    { label: 'Contains', value: '1' },
    { label: 'Begins with', value: '2' }
  ];

  public selected = this.criterias[0].value; // default selected 1 element for a criteria

  constructor(
    private router: Router,
    private formBuilder: FormBuilder,
    public Authentication: AuthenticationService,
    public Auth: AuthService,
    private pagerService: PagerService,
    private Resarvation: NameResarvationService,
    private data: DataService,
    private general: GeneralService,
    private snotifyService: ToastrService,
    private spinner: NgxSpinnerService) {

  }

  ngOnInit() {
    this.current_page = 0;
    // this.__getType();
    this.postfixName = '';
    this.formGroup = this.formBuilder.group({
      postfix: new FormControl(''),
      criteria: new FormControl(this.cRriteria, [
        Validators.required
      ]),
      search: new FormControl(this.search.searchtext, Validators.required)
    });

    this.formGroup.controls['criteria'].setValue(this.selected, { onlySelf: true });
  }

  get criteria() { return this.formGroup.get('criteria'); }

  get searchtext() { return this.formGroup.get('search'); }

  changeCriteria(event: number) {
    this.cRriteria = event;
  }

  ckSearch(): void {

    if (!this.searchtext.value) { this.seResults = undefined; this.meta = undefined; this.pages = undefined; return; }

    this.startFrom = new Date().getTime();
    this.spinner.show();
    const searchData: ISearch = {
      criteria: this.cRriteria,
      searchtext: this.searchtext.value,
      // tslint:disable-next-line:max-line-length
      token: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjBiZjNjZWNjNjQ4MWY3ZWYwZWFlNGZmYzJhMjZjMDMwMWFhYTJjY2U2NWVlMmRiZjdkMjg1NjBjYjZlMTM1ODIyYTQ5MGZiMTdjNDhkYmZiIn0'
    };

    this.Resarvation.getSearchResult(searchData, this.current_page)
      .subscribe(
        req => {
          this.availableData = req['availableData'];
          this.notHasData = req['notHasData'];
          this.searchName = this.searchtext.value;
          this.setPage(this.current_page);
          this.spinner.hide();
        },
        error => {
          this.spinner.hide();
        }
      );
    this.startFrom = (new Date().getTime() - this.startFrom) / 1000.0;
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


  onKeydown($ev) {
    console.log($ev);
  }

  Clean() {
    this.availableData = undefined;
    this.notHasData = undefined;
    this.pages = undefined;
    this.ngOnInit();
  }

}
