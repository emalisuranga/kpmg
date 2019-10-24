import { Component, OnInit } from '@angular/core';
import { ICapital } from 'src/app/http/models/general.model';
import { ReductionCapitalService } from 'src/app/http/services/reduction-capital.service';
import { DataService } from 'src/app/storage/data.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-reduction-stated',
  templateUrl: './reduction-stated.component.html',
  styleUrls: ['./reduction-stated.component.scss']
})
export class ReductionStatedComponent implements OnInit {

  public companyID;
  public capital: Array<ICapital>;

  constructor(
    private reduc: ReductionCapitalService,
    private dataservice: DataService,
    private route: Router
    ) { }

  ngOnInit() {
    this.companyID = this.dataservice.getId;
    localStorage.removeItem('StatedCapitalCompanyId');
    localStorage.setItem('StatedCapitalCompanyId', JSON.stringify(this.dataservice.getId));
    this.reduc.getRecRowData(this.companyID).subscribe(
      req => {
        if (req['status'] === true) {
          this.capital = req['data'];
        }
      }
    );
  }

  getCapital() {
    this.dataservice.setId(this.companyID);
    this.route.navigate(['/dashboard/reduction/capital']);
  }


}
