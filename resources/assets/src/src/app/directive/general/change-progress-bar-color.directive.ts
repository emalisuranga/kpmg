import { Directive, Input, OnInit, ElementRef } from '@angular/core';

@Directive({
  selector: '[appChangeProgressBarColor]'
})
export class ChangeProgressBarColorDirective implements OnInit {

  constructor(private el: ElementRef) { }
  // tslint:disable-next-line:no-input-rename
  @Input('appHighlight') highlightColor: string;

  ngOnInit(): void {

  }



}
