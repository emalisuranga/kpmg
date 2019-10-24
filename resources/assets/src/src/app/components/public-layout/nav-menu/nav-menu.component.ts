import { Component, OnInit } from '@angular/core';
import { environment } from '../../../../environments/environment';

@Component({
  selector: 'app-nav-menu',
  templateUrl: './nav-menu.component.html',
  styleUrls: ['./nav-menu.component.scss']
})
export class NavMenuComponent implements OnInit {

  // incorperationGuidURL = environment.apiUrl + '/pdf/3.6.1(a)Incorporation -Frontend.pdf';
  incorperationGuidURL = environment.apiUrl + 'pdf/3.6.1(a)Incorporation_Frontend.pdf';
  societyGuidURL = environment.apiUrl + 'pdf/3.6.2(a)Society_Frontend.pdf';
  auditorGuidURL = environment.apiUrl + 'pdf/Auditor_user_guide_Public_Ver_1.1.pdf';
  secGuidURL = environment.apiUrl + 'pdf/Secretary_user_guide_Public_Ver_1.1.pdf';
  tenderGuidURL = environment.apiUrl + 'pdf/3.6.5(a)Tender_Frontend.pdf';

  constructor() { }

  ngOnInit() {
  }

}
