import { APIConnection } from '../services/connections/APIConnection';
import { ITitle, ICity, ICountry } from '../models/general.model';
import { DataService } from '../../storage/data.service';
import { GeneralService } from '../services/general.service';
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

@Injectable({
  providedIn: 'root'
})
export class AppLoadService {
  url: APIConnection = new APIConnection();
  constructor(
    private http: HttpClient,
    private dataService: DataService) { }

  async initializeApp(): Promise<ITitle> {
    return this.http.get<ITitle>(this.url.getMemberTitleAPI())
      .toPromise()
      .then(req => {
        return this.dataService.ititles = req;
      });
  }

  async initializeProvince(): Promise<any> {
    return this.http.get<any>(this.url.getCountryDetailsAPI())
      .toPromise()
      .then(req => {
        this.dataService.districts = req['district'];
        this.dataService.citys = req['city'];
        this.dataService.provinces = req['province'];
        this.dataService.payment = req['payment'];
        return req;
      });
  }

  async getCityAndGnDivision(): Promise<any> {
    return this.http.get<ICity>(this.url.getCityAndGnAPI())
    .toPromise()
      .then(req => {
        this.dataService.citys = req['city'];
        this.dataService.gndivisions = req['gndivision'];
        return req;
      });
  }

  getCountry() {
    return  this.http.get<ICountry[]>(this.url.getCountryAPI());
  }
}
