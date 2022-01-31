import dayjs from "dayjs";


// 25 Jan, 2022
export function FriendlyDate({time}) {
    const day = dayjs.unix(time);
    return <span title={day.format("YYYY-MM-DD HH:mm:ss")}>{day.format('MMM D, YYYY')}</span>
}