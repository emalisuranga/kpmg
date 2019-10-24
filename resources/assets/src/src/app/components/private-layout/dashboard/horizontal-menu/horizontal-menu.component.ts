import { Component, OnInit, Output, EventEmitter } from '@angular/core';

@Component({
  selector: 'app-horizontal-menu',
  templateUrl: './horizontal-menu.component.html',
  styleUrls: ['./horizontal-menu.component.scss']
})
export class HorizontalMenuComponent implements OnInit {

  search: string;
  @Output() getName = new EventEmitter();
  constructor() { }

  ngOnInit() {

  }

  onResavation() {
    this.getName.emit(this.search);
  }

}
